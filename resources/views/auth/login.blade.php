<x-layouts.main-layout pageTitle="login">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-6">
                <div class="card p-5">
                    <p class="display-6 text-center">LOGIN</p>

                    <form action="{{ route('authenticate') }}" method="post">
                        @csrf
                        <div class="mb-3">
                            <label for="username" class="form-label">Usuário</label>
                            <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}"><!-- Inserindo o atributo value com a função old() que recupera o valor preenchido -->
                            @error('username')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="password" name="password">
                            @error('password')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mt-4">
                            <div class="col">
                                {{-- <div class="mb-3">
                                    <a href="#">Não tenho conta de usuário</a>
                                </div>
                                <div>
                                    <a href="#">Esqueci a minha senha</a>
                                </div> --}}
                            </div>
                            <div class="col text-end align-self-center">
                                <button type="submit" class="btn btn-secondary px-5">ENTRAR</button>
                            </div>
                        </div>

                    </form>

                    @if (session('invalid_login'))
                        <div class="alert alert-danger text-center mt-3">
                            {{ session('invalid_login') }}
                        </div>
                    @endif


                </div>
            </div>
        </div>
    </div>
</x-layouts.main-layout>
