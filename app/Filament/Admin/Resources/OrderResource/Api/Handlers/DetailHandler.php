<?php

namespace App\Filament\Admin\Resources\OrderResource\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Admin\Resources\OrderResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = OrderResource::class;

    public function handler(Request $request)
    {
        $id = $request->route('id');

        // Get the base query
        $query = static::getEloquentQuery();

        // Fetch the order record with the given ID
        $order = QueryBuilder::for(
            $query->where(static::getKeyName(), $id)
        )
            ->allowedIncludes(['user']) // Allows including related data
            ->first();

        if (!$order) return static::sendNotFoundResponse();

        // Fetch additional data for sender
        $sender = \App\Models\User::select('name as sender_name', 'email as sender_email', 'phone as sender_phone_alternative', 'city_id as sender_city_id')
            ->find($order->sender_id);

        // Fetch additional data for receiver
        $receiver = \App\Models\User::select('name as receiver_name', 'email as receiver_email', 'phone as receiver_phone_alternative', 'city_id as receiver_city_id')
            ->find($order->receive_id);

        // Fetch additional data for receiver
        $pick = \App\Models\User::select('name as pick_name')
        ->find($order->pick_id);

        // Fetch additional data for given
        $given = \App\Models\User::select('name as given_name')
        ->find($order->given_id);

        // Fetch additional data for cities
        $citySource = \App\Models\City::select('name as city_source_name')
            ->find($order->city_source_id);

        $cityTarget = \App\Models\City::select('name as city_target_name')
            ->find($order->city_target_id);

        // Fetch additional data for other IDs (e.g., take_id, delivery_id, etc.)
        $take = \App\Models\User::select('name as take_name', 'email as take_email', 'phone as take_phone_alternative', 'city_id as take_city_id')
            ->find($order->take_id);

        $delivery = \App\Models\User::select('name as delivery_name', 'email as delivery_email', 'phone as delivery_phone_alternative', 'city_id as delivery_city_id')
            ->find($order->delivery_id);

        // Fetch additional data for weight and size from categories table
        $weightCategory = \App\Models\Category::select('name as weight_name')
            ->where('type', 'weight')
            ->find($order->weight_id);

        $sizeCategory = \App\Models\Category::select('name as size_name')
            ->where('type', 'size')
            ->find($order->size_id);


        // Fetch additional data for unit from units table
        $unit = \App\Models\Unit::select('name as unit_name')
        ->find($order->unit_id);

        // Merge sender data
        if ($sender) {
            $order->sender_name = $sender->sender_name;
            $order->sender_email = $sender->sender_email;
            $order->sender_phone_alternative = $sender->sender_phone_alternative;
            $order->sender_city_id = $sender->sender_city_id;
        }

        // Merge receiver data
        if ($receiver) {
            $order->receiver_name = $receiver->receiver_name;
            $order->receiver_email = $receiver->receiver_email;
            $order->receiver_phone_alternative = $receiver->receiver_phone_alternative;
            $order->receiver_city_id = $receiver->receiver_city_id;
        }
        // Merge receiver data
        if ($pick) {
            $order->pick_name = $pick->pick_name;
        }
        // Merge given data
        if ($given) {
            $order->given_name = $given->given_name;
        }
        // Merge city data
        if ($citySource) {
            $order->city_source_name = $citySource->city_source_name;
        }

        if ($cityTarget) {
            $order->city_target_name = $cityTarget->city_target_name;
        }

        // Merge take data
        if ($take) {
            $order->take_name = $take->take_name;
            $order->take_email = $take->take_email;
            $order->take_phone_alternative = $take->take_phone_alternative;
            $order->take_city_id = $take->take_city_id;
        }

        // Merge delivery data
        if ($delivery) {
            $order->delivery_name = $delivery->delivery_name;
            $order->delivery_email = $delivery->delivery_email;
            $order->delivery_phone_alternative = $delivery->delivery_phone_alternative;
            $order->delivery_city_id = $delivery->delivery_city_id;
        }

        // Merge weight and size data
        if ($weightCategory) {
            $order->weight_name = $weightCategory->weight_name;
        }

        if ($sizeCategory) {
            $order->size_name = $sizeCategory->size_name;
        }
        // Merge unit data
        if ($unit) {
            $order->unit_name = $unit->unit_name;
        }
        $transformer = static::getApiTransformer();

        return new $transformer($order);
    }
}
