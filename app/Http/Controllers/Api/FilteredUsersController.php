<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Transformers\SelectlistTransformer;
use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use App\Utilities\UserUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FilteredUsersController extends Controller
{
    public function selectlist(Request $request)
    {
        $ridersUserId = UserUtility::getRiderUsersId();
        $users = User::select(
            [
                'users.id',
                'users.username',
                'users.employee_num',
                'users.first_name',
                'users.last_name',
                'users.gravatar',
                'users.avatar',
                'users.email',
                'users.location_id',
            ]
        )->where('show_in_list', '=', '1')
//            ->join('departments',function ($join){
//                $join->on('departments.user_id','users.id');
//            })
            ->whereNotIn('id',$ridersUserId)
            ->whereHas('department')
            ->where('activated',1)
        ;

//        $users = Company::scopeCompanyables($users);

        if ($request->filled('search')) {
            $users = $users->SimpleNameSearch($request->get('search'))
                ->orWhere('username', 'LIKE', '%'.$request->get('search').'%')
                ->orWhere('employee_num', 'LIKE', '%'.$request->get('search').'%');
        }

        $users = $users->orderBy('last_name', 'asc')->orderBy('first_name', 'asc');
        $users = $users->paginate(50);

        foreach ($users as $user) {
            $name_str = '';
            if ($user->last_name!='') {
                $name_str .= $user->last_name.', ';
            }
            $name_str .= $user->first_name;

            if ($user->username!='') {
                $name_str .= ' ('.$user->username.')';
            }

            if ($user->employee_num!='') {
                $name_str .= ' - #'.$user->employee_num;
            }

            $user->use_text = $name_str;
            $user->use_image = ($user->present()->gravatar) ? $user->present()->gravatar : null;
        }

        return (new SelectlistTransformer)->transformSelectlist($users);
    }
}
