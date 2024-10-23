<?php

namespace App\Http\Controllers;

use App\Mail\NewUserConfirmation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Illuminate\Support\Str; //Inserido manualmente

class AuthController extends Controller
{
    public function login (): View
    {
        return view('auth.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        // Validação do formulário
        $credentials = $request->validate(
            [
                'username' => 'required|min:3|max:30',
                'password' => 'required|min:8|max:32|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
            ],
            [
                'username.required' => 'O usuário é obrigatório',
                'username.min' => 'O usuário deve ter no mínimo :min caracteres',
                'username.max' => 'O usuário deve ter no máximo :max caracteres',
                'password.required' => 'A senha é obrigatória',
                'password.min' => 'A senha deve ter no mínimo :min caracteres',
                'password.max' => 'A senha deve ter no máximo :max caracteres',
                'password.regex' => 'A senha deve conter pelo menos uma letra maiúscula, uma letra minúscula e um número'
            ]
        );

        // //* Login Tradicional do Laravel *//
        // // Da forma tradicional, o laravel utiliza o campo email e password.
        // // Chamando a função abaixo, o laravel realizaria a validação de forma automática, considerando o email e o password como os campos usados para autenticação
        // if(Auth::attempt($credentials)){
        //    // Renovando a sessão
        //    $request->session()->regenerate();
        //    return redirect()->route('home');
        // }


        // Verificar se o user existe
        $user = User::where('username', $credentials['username']) //Onde as credenciais do username sejam iguais a de $credentials->username
                    ->where('active', true) //Onde esse usuário esteja ativo
                    ->where(function($query){ //Onde [abrindo outro conjunto de clausuras, parecido com o parenteses do SQL]
                        //Aonde o campo blocked_until esteja nulo ou seja menor ou igual a data de agora
                        $query->whereNull('blocked_until')
                              ->orWhere('blocked_until', '<=', now());
                    })
                    ->whereNotNull('email_verified_at') //Onde o email tem que ser verificado
                    ->whereNull('deleted_at') //Onde o usuário não tenha sido deletado (softdelete)
                    ->first();

        // Verificar se o user existe
        if(!$user){
            // Voltar ao formulário
            // Retornar os inputs preenchidos
            // retornando uma mensagem:
            return back()->withInput()->with([
                'invalid_login' => 'Login inválido.'
            ]);
        }

        // Verificr se password é válido
        if(!password_verify($credentials['password'], $user->password)){ //Verificando se o password é o mesmo do usuário no banco de dados
            return back()->withInput()->with([
                'invalid_login' => 'Login inválido.'
            ]);
        }
        // atualizar o último login (last_login)
        $user->last_login_at = now();
        $user->blocked_until = null;
        $user->save();

        // login propriamente dito!
        $request->session()->regenerate(); //Renovando o token da sessão
        Auth::login($user);

        // redirecionar
        // Redireciona para a rota chamada ou home se não tiver uma rota específica
        return redirect()->intended(route('home'));
    }

    public function logout(): RedirectResponse
    {
        //logout
        Auth::logout();
        return redirect()->route('login');
    }

    public function register(): View
    {
        return view('auth.register');
    }

    public function store_user(Request $request): RedirectResponse|View
    {
        // Form validation
        $request->validate(
            [
                'username' => 'required|min:3|max:30|unique:users,username',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8|max:32|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                'password_confirmation' => 'required|same:password'
            ],
            [
                'username.required' => 'O usuário é obrigatório.',
                'username.min' => 'O usuário deve ter no mínimo :min caracteres.',
                'username.max' => 'O usuário deve ter no máximo :max caracteres.',
                'username.unique' => 'Este nome não pode ser usado',
                'email.required' => 'O email é obrigatório.',
                'email.email' => 'O email deve ser um endereço de email válido.',
                'email.unique' => 'Este email não pode ser usado',
                'password.required' => 'A senha é obrigatória.',
                'password.min' => 'A senha deve ter no mínimo :min caracteres.',
                'password.max' => 'A senha deve ter no máximo :max caracteres.',
                'password.regex' => 'A senha deve conter pelo menos uma letra maiúscula, uma letra minúscula e um número.',
                'password_confirmation.required' => 'A confirmação de senha é obrigatória.',
                'password_confirmation.same' => 'A confirmação de senha deve ser igual à senha.',
            ]
        );

        // Criando um novo usuário definindo um token de verificação de e-mail
        $user = new User();
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->token = Str::random(64); //Definindo um token rondomico

        // gerar link
        $confirmation_link = route('new_user_confirmation', ['token' => $user->token]);

        // enviar email
        $result = Mail::to($user->email)->send(new NewUserConfirmation($user->username, $confirmation_link));

        // Verificando se o email foi enviado com sucesso
        if(!$result){
            return back()->withInput()->with([
                'server_error' => 'Ocorreu um erro ao enviar o email de confirmação.'
            ]);
        }

        // Criando usuário no banco de dados
        $user->save();

        // Apresentando view de sucesso
        return view('auth.email_sent', ['email' => $user->email]);
    }

    public function new_user_confirmation($token)
    {
        // Verificar se o token é válido
        $user = User::where('token', $token)->first();
        if(!$user){
            // Se o token for inválido, nem vai apresentar mensagem de erro: vai ser redirecionado para login
            return redirect()->route('login');
        }

        // Confirmar o registro do usuário
        $user->email_verified_at = Carbon::now();
        $user->token = null;
        $user->active = true;
        $user->save();

        // Autenticação automática (login) do usuário confirmado
        Auth::login($user);

        // Apresenta uma mensagem de sucesso
        return view('auth.new_user_confirmation');
    }
}
