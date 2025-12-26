<?php

namespace App\Http\Controllers;

use App\Models\Space;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Spaces",
 *     description="Endpoints para gestión de espacios"
 * )
 */
class SpaceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/spaces",
     *     summary="Listar todos los espacios",
     *     tags={"Spaces"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="is_active",
     *         in="query",
     *         description="Filtrar por espacios activos",
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de espacios",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="spaces", type="array", @OA\Items(ref="#/components/schemas/Space"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function index(Request $request)
    {
        $query = Space::query();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $spaces = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'spaces' => $spaces
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/spaces",
     *     summary="Crear un nuevo espacio",
     *     tags={"Spaces"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "capacity"},
     *             @OA\Property(property="name", type="string", example="Sala de Conferencias A"),
     *             @OA\Property(property="description", type="string", example="Sala equipada con proyector"),
     *             @OA\Property(property="capacity", type="integer", example=20),
     *             @OA\Property(property="location", type="string", example="Piso 3"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Espacio creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="space", ref="#/components/schemas/Space")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="No autorizado"),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function store(Request $request)
    {
        // Solo administradores pueden crear espacios
        $user = auth('api')->user();
        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'location' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $space = Space::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Espacio creado exitosamente',
            'data' => [
                'space' => $space
            ]
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/spaces/{id}",
     *     summary="Obtener un espacio específico",
     *     tags={"Spaces"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Espacio encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="space", ref="#/components/schemas/Space")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Espacio no encontrado")
     * )
     */
    public function show($id)
    {
        $space = Space::with('reservations')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'space' => $space
            ]
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/spaces/{id}",
     *     summary="Actualizar un espacio",
     *     tags={"Spaces"},
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
     *             @OA\Property(property="name", type="string", example="Sala de Conferencias A"),
     *             @OA\Property(property="description", type="string", example="Sala equipada con proyector"),
     *             @OA\Property(property="capacity", type="integer", example=20),
     *             @OA\Property(property="location", type="string", example="Piso 3"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Espacio actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="space", ref="#/components/schemas/Space")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="No autorizado"),
     *     @OA\Response(response=404, description="Espacio no encontrado"),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function update(Request $request, $id)
    {
        // Solo administradores pueden actualizar espacios
        $user = auth('api')->user();
        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado'
            ], 403);
        }

        $space = Space::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'sometimes|required|integer|min:1',
            'location' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $space->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Espacio actualizado exitosamente',
            'data' => [
                'space' => $space->fresh()
            ]
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/spaces/{id}",
     *     summary="Eliminar un espacio",
     *     tags={"Spaces"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Espacio eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Espacio eliminado exitosamente")
     *         )
     *     ),
     *     @OA\Response(response=403, description="No autorizado"),
     *     @OA\Response(response=404, description="Espacio no encontrado")
     * )
     */
    public function destroy($id)
    {
        // Solo administradores pueden eliminar espacios
        $user = auth('api')->user();
        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado'
            ], 403);
        }

        $space = Space::findOrFail($id);
        $space->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Espacio eliminado exitosamente'
        ]);
    }
}

