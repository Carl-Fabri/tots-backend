<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @OA\Schema(
 *     schema="Space",
 *     type="object",
 *     title="Space",
 *     description="Espacio disponible para reservar",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Sala de Conferencias A"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Sala equipada con proyector y capacidad para 20 personas"),
 *     @OA\Property(property="capacity", type="integer", example=20),
 *     @OA\Property(property="location", type="string", example="Piso 3, Edificio Principal"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="reservations", type="array", @OA\Items(ref="#/components/schemas/Reservation"))
 * )
 */
class Space extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'capacity',
        'location',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relación con las reservas
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}

