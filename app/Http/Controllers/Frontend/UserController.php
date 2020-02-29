<?php
namespace App\Http\Controllers\Frontend;


use Illuminate\Http\Request;

use App\Repositories\User\UserInterface as UserInterface;
use App\Http\Controllers\Controller;

class UserController extends Controller

{


    public function __construct(UserInterface $user)

    {

        $this->user = $user;

    }


    /**

     * Display a listing of the resource.

     *

     * @return \Illuminate\Http\Response

     */

    public function index()

    {
//        dd(123);
//        $users = $this->user->getAll();

        return view('dashboard');

    }

      public function appointments()

    {
//        dd(123);
//        $users = $this->user->getAll();

        return view('frontend.appointment');

    }
    
          public function userregister()

    {
        return view('frontend.registeration');

    }
          public function doctorpanel()

    {
        return view('frontend.doctorpanel');

    }

    /**

     * Display a listing of the resource.

     *

     * @return \Illuminate\Http\Response

     */

    public function show($id)

    {

        $user = $this->user->find($id);

        return view('users.show',['user']);

    }


    /**

     * Display a listing of the resource.

     *

     * @return \Illuminate\Http\Response

     */

    public function delete($id)

    {

        $this->user->delete($id);

        return redirect()->route('users');

    }

}