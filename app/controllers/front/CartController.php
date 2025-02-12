<?php
namespace App\controllers\front;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\enums\Role;
use App\models\Ticket;

class CartController extends Controller{

    public function index(){
        Auth::requireAuth(Role::PARTICIPANT);

        $reservations = Ticket::getByUserId(Session::getUser()['id']);

        $total_price = 80;

        $this->view('front/cart',['reservations'=>$reservations ,'total_price'=> $total_price]);
    }

    public function confirmReservation($id){
        echo "comfirm $id"; // TODO:
    }

    public function cancelReservation($id){
        echo "cancel $id"; // TODO:
    } 

}
