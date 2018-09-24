<?php namespace Api\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class User
 * @package Api\Models
 *
 * @property int id
 * @property string username
 * @property string password
 */
class User extends Model
{
    public function __construct(array $attributes = [])
    {
        $this->table = 'users';
        $this->timestamps = false;
        $this->fillable = [
            'username',
            'password',
            'firstName',
            'lastName'
        ];
        $this->hidden = ['id', 'username', 'password'];

        parent::__construct($attributes);
    }
}
