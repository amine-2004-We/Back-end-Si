<?php

namespace App\Http\Requests;

use App\Enums\TransportMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRouteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'route_code'              => ['nullable', 'string', 'max:255', 'unique:routes,route_code'],
            'departure_location_name' => ['required', 'string', 'max:255'],
            'departure_latitude'      => ['nullable', 'numeric', 'between:-90,90'],
            'departure_longitude'     => ['nullable', 'numeric', 'between:-180,180'],
            'arrival_location_name'   => ['required', 'string', 'max:255'],
            'arrival_latitude'        => ['nullable', 'numeric', 'between:-90,90'],
            'arrival_longitude'       => ['nullable', 'numeric', 'between:-180,180'],
            'transport_mode'          => ['required', Rule::in(TransportMode::values())],
            'rate'                    => ['required', 'numeric', 'min:0'],
            'scale_price'             => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get the custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'route_code.unique'                 => 'Ce code trajet est déjà utilisé.',
            'departure_location_name.required'  => 'Le nom du lieu de départ est requis.',
            'arrival_location_name.required'    => 'Le nom du lieu d\'arrivée est requis.',
            'transport_mode.required'           => 'Le moyen de transport est requis.',
            'transport_mode.in'                 => 'Le moyen de transport sélectionné est invalide.',
            'rate.required'                     => 'La tarification est requise.',
            'rate.numeric'                      => 'La tarification doit être un nombre.',
            'scale_price.required'              => 'Le barème est requis.',
            'scale_price.numeric'               => 'Le barème doit être un nombre.',
            'departure_latitude.numeric'        => 'La latitude de départ doit être un nombre.',
            'departure_longitude.numeric'       => 'La longitude de départ doit être un nombre.',
            'arrival_latitude.numeric'          => 'La latitude d\'arrivée doit être un nombre.',
            'arrival_longitude.numeric'         => 'La longitude d\'arrivée doit être un nombre.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'route_code'              => 'code trajet',
            'departure_location_name' => 'nom du lieu de départ',
            'departure_latitude'      => 'latitude de départ',
            'departure_longitude'     => 'longitude de départ',
            'arrival_location_name'   => 'nom du lieu d\'arrivée',
            'arrival_latitude'        => 'latitude d\'arrivée',
            'arrival_longitude'       => 'longitude d\'arrivée',
            'transport_mode'          => 'moyen de transport',
            'rate'                    => 'tarification',
            'scale_price'             => 'barème',
        ];
    }
}

