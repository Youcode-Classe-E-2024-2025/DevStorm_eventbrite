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

        $this->view('front/cart',[
            'reservations'=>$reservations ,
            'total_price'=> $total_price,
            'green' =>Session::getFlashMessage('green'),
            'red' => Session::getFlashMessage('red')
        ]);
    }

    public function cancelReservation($id){

        if (empty($id) || !is_numeric($id)) {
            $this->redirect('/404');
        }
        $ticket = Ticket::read($id);
        if (!$ticket) {
            Session::setFlashMessage('red','reservation not found.');
            $this->redirect('/cart');
        }
        if($ticket->status == 'validé'){
            Session::setFlashMessage('red','cannot cancel , reservation already paid.');
            $this->redirect('/cart');
        }
       if( $ticket->delete()){
        Session::setFlashMessage('green',' reservation canceled .');
        $this->redirect('/cart');
       }else{
        Session::setFlashMessage('red','cancel reservation  failed.');
        $this->redirect('/cart');
       }
    } 

    public function showTicket($id){
        if (empty($id) || !is_numeric($id)) {
            $this->redirect('/404');
        }
        $ticket = Ticket::read($id);
        if (!$ticket) {
            Session::setFlashMessage('red','Ticket not found.');
            $this->redirect('/cart');
        }
        if($ticket->user->id !== Session::getUser()['id']){
            Session::setFlashMessage('red','Permission denied.');
            $this->redirect('/403');
        }
        if($ticket->status != 'validé'){
            Session::setFlashMessage('red','cannot access , reservation not paid.');
            $this->redirect('/cart');
        }

        $this->view('front/event/ticket',[
            'ticket'=>$ticket ,
            'green' =>Session::getFlashMessage('green'),
            'red' => Session::getFlashMessage('red')
        ]);

    }

}
