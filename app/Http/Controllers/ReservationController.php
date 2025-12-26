<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Space;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Reservations",
 *     description="Endpoints para gestión de reservas"
 * )
 */
class ReservationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/reservations",
     *     summary="Listar reservas del usuario autenticado",
     *     tags={"Reservations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="space_id",
     *         in="query",
     *         description="Filtrar por espacio",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio para filtrar (formato: Y-m-d)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin para filtrar (formato: Y-m-d)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrar por estado",
     *         @OA\Schema(type="string", enum={"pending", "confirmed", "cancelled"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de reservas",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="reservations", type="array", @OA\Items(ref="#/components/schemas/Reservation"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function index(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autenticado'
            ], 401);
        }

        $query = Reservation::where('user_id', $user->id)
            ->with(['space', 'user']);

        if ($request->has('space_id')) {
            $query->where('space_id', $request->space_id);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->inDateRange($startDate, $endDate);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $reservations = $query->orderBy('start_time')->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'reservations' => $reservations
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/reservations/calendar",
     *     summary="Obtener reservas para calendario (todos los espacios)",
     *     tags={"Reservations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=true,
     *         description="Fecha de inicio (formato: Y-m-d)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=true,
     *         description="Fecha de fin (formato: Y-m-d)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="space_id",
     *         in="query",
     *         description="Filtrar por espacio específico",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reservas para calendario",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="reservations", type="array", @OA\Items(ref="#/components/schemas/Reservation"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function calendar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'space_id' => 'nullable|exists:spaces,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $query = Reservation::confirmed()
            ->inDateRange($startDate, $endDate)
            ->with(['space', 'user']);

        if ($request->has('space_id')) {
            $query->forSpace($request->space_id);
        }

        $reservations = $query->orderBy('start_time')->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'reservations' => $reservations
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/reservations",
     *     summary="Crear una nueva reserva",
     *     tags={"Reservations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"space_id", "title", "start_time", "end_time"},
     *             @OA\Property(property="space_id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Reunión de equipo"),
     *             @OA\Property(property="description", type="string", example="Reunión semanal"),
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2024-01-15 10:00:00"),
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2024-01-15 11:00:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Reserva creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="reservation", ref="#/components/schemas/Reservation")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Superposición de reservas"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'space_id' => 'required|exists:spaces,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que el espacio existe y está activo
        $space = Space::findOrFail($request->space_id);
        if (!$space->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'El espacio no está disponible'
            ], 400);
        }

        // Validar superposición de reservas
        $startTime = Carbon::parse($request->start_time);
        $endTime = Carbon::parse($request->end_time);

        $overlappingReservation = Reservation::where('space_id', $request->space_id)
            ->where('status', 'confirmed')
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                          ->where('end_time', '>=', $endTime);
                    });
            })
            ->first();

        if ($overlappingReservation) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ya existe una reserva en este horario para este espacio',
                'data' => [
                    'overlapping_reservation' => $overlappingReservation
                ]
            ], 400);
        }

        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autenticado'
            ], 401);
        }

        $reservation = Reservation::create([
            'user_id' => $user->id,
            'space_id' => $request->space_id,
            'title' => $request->title,
            'description' => $request->description,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'confirmed',
        ]);

        $reservation->load(['space', 'user']);

        return response()->json([
            'status' => 'success',
            'message' => 'Reserva creada exitosamente',
            'data' => [
                'reservation' => $reservation
            ]
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/reservations/{id}",
     *     summary="Obtener una reserva específica",
     *     tags={"Reservations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reserva encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="reservation", ref="#/components/schemas/Reservation")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="No autorizado"),
     *     @OA\Response(response=404, description="Reserva no encontrada")
     * )
     */
    public function show($id)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autenticado'
            ], 401);
        }

        $reservation = Reservation::with(['space', 'user'])->findOrFail($id);

        // Solo el dueño de la reserva puede verla
        if ($reservation->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'reservation' => $reservation
            ]
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/reservations/{id}",
     *     summary="Actualizar una reserva",
     *     tags={"Reservations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="space_id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Reunión de equipo"),
     *             @OA\Property(property="description", type="string", example="Reunión semanal"),
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2024-01-15 10:00:00"),
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2024-01-15 11:00:00"),
     *             @OA\Property(property="status", type="string", enum={"pending", "confirmed", "cancelled"}, example="confirmed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reserva actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="reservation", ref="#/components/schemas/Reservation")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Superposición de reservas"),
     *     @OA\Response(response=403, description="No autorizado"),
     *     @OA\Response(response=404, description="Reserva no encontrada"),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function update(Request $request, $id)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autenticado'
            ], 401);
        }

        $reservation = Reservation::findOrFail($id);

        // Solo el dueño de la reserva puede actualizarla
        if ($reservation->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'space_id' => 'sometimes|required|exists:spaces,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'sometimes|required|date',
            'end_time' => 'sometimes|required|date|after:start_time',
            'status' => 'sometimes|in:pending,confirmed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Si se cambia el espacio o el horario, validar superposición
        $spaceId = $request->get('space_id', $reservation->space_id);
        $startTime = $request->has('start_time') ? Carbon::parse($request->start_time) : $reservation->start_time;
        $endTime = $request->has('end_time') ? Carbon::parse($request->end_time) : $reservation->end_time;

        if ($request->has('space_id') || $request->has('start_time') || $request->has('end_time')) {
            // Verificar que el espacio existe y está activo
            $space = Space::findOrFail($spaceId);
            if (!$space->is_active) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El espacio no está disponible'
                ], 400);
            }

            // Validar superposición (excluyendo la reserva actual)
            $overlappingReservation = Reservation::where('space_id', $spaceId)
                ->where('id', '!=', $id)
                ->where('status', 'confirmed')
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->whereBetween('start_time', [$startTime, $endTime])
                        ->orWhereBetween('end_time', [$startTime, $endTime])
                        ->orWhere(function ($q) use ($startTime, $endTime) {
                            $q->where('start_time', '<=', $startTime)
                              ->where('end_time', '>=', $endTime);
                        });
                })
                ->first();

            if ($overlappingReservation) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ya existe una reserva en este horario para este espacio',
                    'data' => [
                        'overlapping_reservation' => $overlappingReservation
                    ]
                ], 400);
            }
        }

        $reservation->update($request->all());
        $reservation->load(['space', 'user']);

        return response()->json([
            'status' => 'success',
            'message' => 'Reserva actualizada exitosamente',
            'data' => [
                'reservation' => $reservation->fresh()
            ]
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/reservations/{id}",
     *     summary="Eliminar una reserva",
     *     tags={"Reservations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reserva eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Reserva eliminada exitosamente")
     *         )
     *     ),
     *     @OA\Response(response=403, description="No autorizado"),
     *     @OA\Response(response=404, description="Reserva no encontrada")
     * )
     */
    public function destroy($id)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autenticado'
            ], 401);
        }

        $reservation = Reservation::findOrFail($id);

        // Solo el dueño de la reserva puede eliminarla
        if ($reservation->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado'
            ], 403);
        }

        $reservation->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Reserva eliminada exitosamente'
        ]);
    }
}

