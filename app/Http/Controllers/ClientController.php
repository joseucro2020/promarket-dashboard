<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pais;
use App\Models\Estado;
use App\Models\Municipality;
use App\Models\Parish;
use Carbon\Carbon;
use Hash;
use Lang;
use Maatwebsite\Excel\Facades\Excel;
use Validator;
use App\Models\Referral;
use App\Models\Coupon;
use App\Exports\ClientExport;
use App\Services\PhoneNormalizationService;

class ClientController extends Controller
{
    public function index()
    {
        $clients = User::with([
            'pais',
            'estado',
            'municipality',
            'parish',
            'pedidos_lastest.details',
            'pedidos_lastest.exchange'
        ])->where('nivel', '1')
            ->where('pro_seller', User::IS_NOT_PRO)
            ->latest()
            ->get();

            // dd($clients);

        $states = Estado::where('pais_id', Pais::VENEZUELA_ID)->get();

        return view('panel.clients.index', ['clients' => $clients, 'states' => $states]);
    }

    public function changeStatus(Request $request, $id)
    {
        $clients = User::find($id);
        $clients->status = $request->status;
        $clients->save();

        Referral::where('referrer_id', $id)->delete();
        Coupon::where('user_id', $id)->update([
            'status' => Coupon::STATUS_INACTIVE
        ]);

        return response()->json(['result' => true]);
    }

    public function delete(Request $request, $id)
    {
        $client = User::find($id);
        $client->status = $request->status;
        $client->save();

        Referral::where('referrer_id', $id)->delete();
        Coupon::where('user_id', $id)->update([
            'status' => Coupon::STATUS_INACTIVE
        ]);

        return response()->json(['result' => true]);
    }

    public function convertToPro($id)
    {
        $client = User::find($id);
        $client->pro_seller = User::IS_PRO;
        $client->save();

        Referral::where('referred_id', $id)->delete();

        return response()->json(['result' => true]);
    }

    public function getAll()
    {
        $clients = User::where('nivel', '1')->where('pro_seller', User::IS_NOT_PRO)
            ->with('pais', 'estado', 'pedidos_lastest.details', 'pedidos_lastest.exchange')->get();
        return $clients;
    }

    public function getAllServer(Request $request)
    {
        $columns = [
            0 => 'name',
            1 => 'identificacion',
            2 => 'persona',
            3 => 'created_at',
            4 => 'telefono',
            5 => 'status'
        ];

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $draw = intval($request->input('draw'));

        $baseQuery = User::where('nivel', '1')->where('pro_seller', User::IS_NOT_PRO);

        $recordsTotal = $baseQuery->count();

        // Apply search
        $search = $request->input('search.value');
        if (!empty($search)) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('identificacion', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = $baseQuery->count();

        // Ordering
        $orderColumnIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir', 'desc');
        if ($orderColumnIndex !== null && isset($columns[intval($orderColumnIndex)])) {
            $orderColumn = $columns[intval($orderColumnIndex)];
            $baseQuery->orderBy($orderColumn, $orderDir);
        } else {
            $baseQuery->latest();
        }

        $data = $baseQuery->offset($start)->limit($length)
            ->get(['id', 'name', 'identificacion', 'persona', 'created_at', 'telefono', 'status']);

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function edit($id)
    {
        $client = User::with(['estado', 'municipality', 'parish'])
            ->where('nivel', '1')
            ->where('pro_seller', User::IS_NOT_PRO)
            ->findOrFail($id);

        $states = Estado::where('pais_id', Pais::VENEZUELA_ID)
            ->orderBy('nombre', 'asc')
            ->get();

        $municipalities = Municipality::where('estado_id', $client->estado_id)
            ->orderBy('name', 'asc')
            ->get();

        $parishes = Parish::where('municipality_id', $client->municipality_id)
            ->orderBy('name', 'asc')
            ->get();

        return view('panel.clients.edit', [
            'client' => $client,
            'states' => $states,
            'municipalities' => $municipalities,
            'parishes' => $parishes,
        ]);
    }

    public function getParishes($municipalityId)
    {
        $parishes = Parish::where('municipality_id', $municipalityId)
            ->orderBy('name', 'asc')
            ->get();

        return response()->json($parishes);
    }

    public function getOrders($id)
    {
        $orders = \App\Models\Purchase::where('user_id', $id)
            ->with(['details', 'exchange'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'result' => true,
            'data' => $orders
        ]);
    }

    public function update(Request $request)
    {
        $reglas = [
            'name' => 'required',
            'type' => 'required',
            'identificacion' => 'required|numeric',
            'empresa' => 'required_if:type,2',
            'fiscal' => 'required_if:type,2',
            'email' => 'required|email',
            'telefono_country_code' => 'nullable|in:58,1,52,57,54,56,51,593,595,598,591,34,507,506,503,502,504,505,53',
            'telefono' => 'nullable|string|max:30',
            'estado_id' => 'required',
            'municipality_id' => 'required',
            'parish_id' => 'required',
            'direccion' => 'required',
            'password' => 'confirmed'
        ];
        $atributos = [
            'name' => Lang::get('Controllers.Atributos.Nombre'),
            'email' => Lang::get('Controllers.Atributos.Email'),
            'type' => Lang::get('Controllers.Atributos.Tipo'),
            'identificacion' => Lang::get('Controllers.Atributos.Identificacion'),
            'telefono' => Lang::get('Controllers.Atributos.Telefono'),
            'estado' => Lang::get('Controllers.Atributos.Estado'),
            'direccion' => Lang::get('Controllers.Atributos.Direccion'),
            'password' => Lang::get('Controllers.Atributos.Password'),
            'estado_id' => Lang::get('Controllers.Atributos.Estado'),
            'municipality_id' => Lang::get('Controllers.Atributos.Municipio'),
            'parish_id' => Lang::get('Controllers.Atributos.Parroquia'),
            'fiscal' => 'dirección fiscal',
            'empresa' =>  Lang::get('Controllers.Empresa')
        ];
        $validacion = Validator::make($request->all(), $reglas);
        $validacion->setAttributeNames($atributos);
        if ($validacion->fails()) {
            return response()->json([
                'error' => $validacion->messages()->first()
            ], 422);
        }

        $exists = User::where('id', '!=', $request->id)->where('email', $request->email)->exists();

        if ($exists) {
            return response()->json([
                'error' => 'El correo electrónico ya se encuentar registrado'
            ], 422);
        }

        $current_user = User::where('id', $request->id)
            ->where('status', '!=', '2')->first();

        $users = User::where('id', '!=', $request->id)
            ->where('status', '!=', '2')->get()->pluck('id');

        if ($current_user->email != $request->email) {
            $check_email = User::whereIn('id', $users)->where('status', '!=', '2')->get();
            foreach ($check_email as $check) {
                if ($check->email == $request->email) {
                    return response()->json([
                        'error' => 'Ya esta registrado este Correo Electronico'
                    ], 422);
                }
            }
        }

        $normalizedPhone = null;
        $telefonoCountryCode = $request->input('telefono_country_code', '58');
        if ($request->filled('telefono')) {
            $normalizedPhone = app(PhoneNormalizationService::class)->normalizeWhatsappVe($request->telefono, $telefonoCountryCode);

            if ($normalizedPhone === null) {
                return response()->json([
                    'error' => 'El teléfono debe tener un formato válido para WhatsApp internacional.'
                ], 422);
            }
        }

        $client = User::find($request->id);
        $client->name = $request->name;
        $client->email = $request->email;
        $client->persona = $request->type == '2' ? USER::JURIDICO : USER::NATURAL;
        $client->type = USER::NATURAL;
        $client->identificacion = $request->identificacion;
        $client->telefono = $normalizedPhone ?: $request->telefono;
        $client->telefono_whatsapp = $normalizedPhone;
        $client->pais_id = Pais::VENEZUELA_ID;
        $client->estado_id = $request->estado_id;
        $client->municipality_id = $request->municipality_id;
        $client->parish_id = $request->parish_id;
        $client->direccion = $request->direccion;
        if ($request->has('password')) {
            $client->password = Hash::make($request->password);
        }
        $client->empresa = $request->empresa;
        $client->fiscal = $request->has('fiscal') ? $request->fiscal : '';
        $client->referencia = $request->has('referencia') ? $request->referencia : '';
        $client->save();

        return response()->json(['result' => true]);
    }



    public function exportExcel(Request $request)
    {
        $columns = [
            0 => 'name',
            1 => 'identificacion',
            2 => 'persona',
            3 => 'created_at',
            4 => 'telefono',
            5 => 'status'
        ];

        $query = User::where('nivel', '1')
            ->where('pro_seller', User::IS_NOT_PRO)
            ->with(['estado', 'municipality', 'parish']);

        $search = $request->input('search.value', $request->input('search_value'));
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('identificacion', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        $orderColumnIndex = $request->input('order.0.column', $request->input('order_column'));
        $orderDir = $request->input('order.0.dir', $request->input('order_dir', 'desc'));
        $orderDir = strtolower($orderDir) === 'asc' ? 'asc' : 'desc';

        if ($orderColumnIndex !== null && isset($columns[intval($orderColumnIndex)])) {
            $query->orderBy($columns[intval($orderColumnIndex)], $orderDir);
        } else {
            $query->latest();
        }

        $data = $query->get([
            'id',
            'name',
            'identificacion',
            'persona',
            'created_at',
            'telefono',
            'status',
            'email',
            'direccion',
            'estado_id',
            'municipality_id',
            'parish_id'
        ]);

        $today = now()->format('d-m-Y h:i A');

        return Excel::download(new ClientExport($data, $today), 'Reporte_Clientes.xls');
    }
}
