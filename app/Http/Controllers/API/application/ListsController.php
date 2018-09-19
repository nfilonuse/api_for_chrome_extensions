<?php

namespace App\Http\Controllers\API\application;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\APIController as APIController;
use Illuminate\Http\Request;

use App\Models\Country;
use App\Models\State;

class ListsController extends Controller
{
    private $per_page = 10;
    /**
     * Create a new CompanyController instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('jwt.auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCountries(Request $request)
    {
        $countries=new Country();
        if ($request->has('s')&&!empty($request->s)&&strlen($request->s)>=3)
        {
            if (strlen($request->s)>=3)
                $countries=$countries->where('name','like', '%' . $request->s . '%');
            else
            {
                $data = [
                    'states'=> [],
                    'total'=> 0,
                ];
                return APIController::json_success($data);
            }
        }
        if ($request->has('per_page'))
        {
            $countries=$countries->paginate($request->per_page);
        }
        else
        {
            $countries=$countries->paginate($this->per_page);
        }
		$data = [
			'countries'=> $countries->toarray()['data'],
        ];
		return APIController::json_success($data);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getStates(Request $request,$country_id)
    {
        $states=State::where('country_id',$country_id);
        if ($request->has('s')&&(!empty($request->s)))
        {
            if (strlen($request->s)>=3)
                $states=$states->where('name','like', '%' . $request->s . '%');
            else
            {
                $data = [
                    'states'=> [],
                    'total'=> 0,
                ];
                return APIController::json_success($data);
            }
        }
        if ($request->has('per_page'))
        {
            $states=$states->paginate($request->per_page);
        }
        else
        {
            $states=$states->paginate($this->per_page);
        }
		$data = [
			'states'=> $states->toarray()['data'],
			'total'=> $states->toarray()['total'],
        ];
		return APIController::json_success($data);
    }
}
