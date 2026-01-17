<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pais;
use App\Models\Estado;
use Carbon\Carbon;
use Hash;
use Lang;
use Maatwebsite\Excel\Facades\Excel;
use Validator;
use App\Models\Referral;
use App\Models\Coupon;
use App\Exports\ClientExport;

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

    public function update(Request $request)
    {
        $reglas = [
            'name' => 'required',
            'type' => 'required',
            'identificacion' => 'required|numeric',
            'empresa' => 'required_if:type,2',
            'fiscal' => 'required_if:type,2',
            'email' => 'required|email',
            'telefono' => 'nullable|numeric',
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
        $client = User::find($request->id);
        $client->name = $request->name;
        $client->email = $request->email;
        $client->persona = $request->type == '2' ? USER::JURIDICO : USER::NATURAL;
        $client->type = USER::NATURAL;
        $client->identificacion = $request->identificacion;
        $client->telefono = $request->telefono;
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
        $data = $request->input('data', []);
        $today = now()->format('d-m-Y h:i A');

        return Excel::download(new ClientExport($data, $today), 'Reporte_Clientes.xls');
    }
}
