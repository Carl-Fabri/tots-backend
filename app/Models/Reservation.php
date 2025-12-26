<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="Reservation",
 *     type="object",
 *     title="Reservation",
 *     description="Reserva de un espacio",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="space_id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Reunión de equipo"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Reunión semanal del equipo de desarrollo"),
 *     @OA\Property(property="start_time", type="string", format="date-time", example="2024-01-15 10:00:00"),
 *     @OA\Property(property="end_time", type="string", format="date-time", example="2024-01-15 11:00:00"),
 *     @OA\Property(property="status", type="string", enum={"pending", "confirmed", "cancelled"}, example="confirmed"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="user", ref="#/components/schemas/User"),
 *     @OA\Property(property="space", ref="#/components/schemas/Space")
 * )
 */
class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'space_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el espacio
     */
    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    /**
     * Scope para filtrar reservas por rango de fechas (útil para calendarios)
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_time', [$startDate, $endDate])
              ->orWhereBetween('end_time', [$startDate, $endDate])
              ->orWhere(function ($subQ) use ($startDate, $endDate) {
                  $subQ->where('start_time', '<=', $startDate)
                        ->where('end_time', '>=', $endDate);
              });
        });
    }

    /**
     * Scope para filtrar reservas por espacio
     */
    public function scopeForSpace($query, $spaceId)
    {
        return $query->where('space_id', $spaceId);
    }

    /**
     * Scope para filtrar reservas confirmadas
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }
}

