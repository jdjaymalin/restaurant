<?php namespace Database;

use Api\Models\Restaurant;
use Api\Models\User;
use Illuminate\Database\Capsule\Manager as DB;

class Migration
{
    public static function up()
    {
        if (!DB::schema()->hasTable('users')) {
            DB::schema()->create('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->increments('id');
                $table->string('username')->unique();
                $table->string('firstName');
                $table->string('lastName');
                $table->string('password');
            });
        }

        if (!DB::schema()->hasTable('restaurants')) {
            DB::schema()->create('restaurants', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->boolean('hasVegetarian');
                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            });
        }

    }

    public static function down()
    {
        DB::schema()->dropIfExists('restaurants');
        DB::schema()->dropIfExists('users');
    }

    public static function seed()
    {
        $user = User::create([
            'username' => 'test',
            'password' => password_hash('password', PASSWORD_BCRYPT),
            'firstName' => 'Test',
            'lastName' => 'Test'
        ]);

        /** @var Restaurant $restaurant */
        $restaurant = new Restaurant([
            'name' => 'Nepalico',
            'hasVegetarian' => true
        ]);
        $restaurant->author()->associate($user);
        $restaurant->save();

        /** @var Restaurant $restaurant */
        $restaurant = new Restaurant([
            'name' => 'Cento Anni',
            'hasVegetarian' => true
        ]);
        $restaurant->author()->associate($user);
        $restaurant->save();

        /** @var Restaurant $restaurant */
        $restaurant = new Restaurant([
            'name' => 'Nagi Shokudo',
            'hasVegetarian' => true
        ]);
        $restaurant->author()->associate($user);
        $restaurant->save();

        /** @var Restaurant $restaurant */
        $restaurant = new Restaurant([
            'name' => 'Yamagata',
            'hasVegetarian' => false
        ]);
        $restaurant->author()->associate($user);
        $restaurant->save();
    }
}
