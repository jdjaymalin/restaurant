<?php namespace Api\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Restaurant
 * @package Api\Models
 *
 * @property int id
 * @property string name
 * @property int user_id
 * @property int rating
 * @property Rating[]|Collection $ratings
 *
 *  @method static static|Builder find($id)
 *  @method static static|Builder nameSearch($search)
 *  @method Builder hasVegetarian($isVegetarian)
 *  @method Builder hasRating($rating)
 */
class Restaurant extends Model
{
    public function __construct(array $attributes = [])
    {
        $this->table = 'restaurants';
        $this->timestamps = false;
        $this->fillable = [
            'name',
            'hasVegetarian'
        ];
        $this->hidden = ['user_id'];


        parent::__construct($attributes);
    }

    public function author() : BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getRatingAttribute() : float
    {
        $avg = $this->ratings->average('rating') ?? 0;

        return number_format($avg, 2);
    }

    /**
     * @param Builder $query
     * @param int $search
     * @return static|Builder
     */
    public function scopeNameSearch(Builder $query, $search)
    {
        return $query->where('name', 'ilike', '%' . $search . '%');
    }

    /**
     * @param Builder $query
     * @param bool $isVegetarian
     * @return static|Builder
     */
    public function scopeHasVegetarian(Builder $query, bool $isVegetarian)
    {
        return $query->where('hasVegetarian', $isVegetarian);
    }
}
