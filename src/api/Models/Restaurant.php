<?php namespace Api\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Restaurant
 * @package Api\Models
 *
 * @property int id
 * @property string name
 * @property int user_id
 *
 *  @method static static|Builder find($id)
 *  @method static static|Builder nameSearch($search)
 *  @method Builder hasVegetarian($isVegetarian)
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
