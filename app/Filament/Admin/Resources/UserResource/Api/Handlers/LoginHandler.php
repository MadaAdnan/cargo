<?php

namespace App\Filament\Admin\Resources\UserResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Admin\Resources\UserResource;
use Illuminate\Support\Facades\Hash;

class LoginHandler extends Handlers
{
    public static string | null $uri = '/verify';
    public static string | null $resource = UserResource::class;
    
    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel()
    {
        return static::$resource::getModel();
    }

    public function handler(Request $request)
    {
        // Validate the input
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Get the model
        $model = static::getModel();

        // Find the user by email
        $user = $model::where('email', $validated['email'])->first();

        // Check if user exists and the password matches
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return static::sendNotFoundResponse('Invalid email or password', 401);
        }
        // Add city.name dynamically if city exists
        //$user->setAttribute('city_name', $user->city ? $user->city->name : null);
        // Exclude the password column
        $user->makeHidden(['password']);

        // Return the success response
        return static::sendSuccessResponse($user, 'Login successful');
    }
}
/*

// Validate the input
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Get the model
        $model = static::getModel();

        // Find the user by email and eager load the 'city' relationship
        $user = $model::with('city')->where('email', $validated['email'])->first();

        // Check if user exists and the password matches
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return static::sendNotFoundResponse('Invalid email or password', 401);
        }

        // Exclude the password column
        $user->makeHidden(['password', 'city_id']); // Hide city_id if you don't need it

        // Add city.name dynamically if city exists
        $user->setAttribute('city_name', $user->city ? $user->city->name : null);

        // Return the success response
        return static::sendSuccessResponse($user, 'Login successful');
*/