<html>

<body>
<div class="main-content-inner">
    <div class="row">

    @if ( session()->has('success') )
    <div class="alert-dismiss col-sm-12">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session()->get('success') }}
        </div>
    </div>
    @endif

    @if(session()->has('error'))
    <div class="alert-dismiss col-sm-12">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session()->get('error') }}
        </div>
    </div>
    @endif

    <!-- title and desc -->
    <div class="col-sm-12 mt-2 text-gray-600 dark:text-gray-400 text-sm">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Generaci&oacute;n de token</h5>
        <p class="card-text">Genere el token de Google necesario para enviar los correos electr&oacute;nicos usando la cuenta corporativa. El sistema autom&aacute;ticamente renovar&aacute; el token previo a los env&iacute;os de los correos. Use esta opci&oacute;n cuando el token se haya vencido y no sea posible renovarlo.</p>
        <form action="{{ route('generate.token') }}" method="post" class="mr-2">
        @csrf
        <button type="submit" class="cursor btn btn-primary p-2 px-6 bg-gray-900 text-gray-600 font-semibold">Generar Token</button>
        </form>
      </div>
    </div>
    </div>

    <!-- current token -->
    <div class="col-sm-12 col-md-6 mt-2 text-gray-600 dark:text-gray-400 text-sm">
    <div class="card">
      <div class="card-body">
      @if(session()->has('success-renew'))
      <div class="alert-dismiss col-sm-12">
          <div class="alert alert-success alert-dismissible fade show" role="alert">
              {{ session()->get('success-renew') }}
          </div>
      </div>
      @endif
        <h5 class="card-title">Token actual&nbsp;</h5>
        <form action="{{ route('generate.new-token') }}" method="post" class="mr-2 pull-right">
        @csrf
        <button type="submit" class="cursor btn btn-primary p-2 px-6 bg-gray-900 text-gray-600 font-semibold">Renovar</button>
        </form>
        <p class="card-text">Token provisto por Google actualmente.</p>
        <p class="card-text">Token:&nbsp;<span id="current_token"></span></p>
        <p class="card-text">V&aacute;lido hasta:&nbsp;<span id="current_token_expires"></span></p>
        <hr/>
        <p class="card-text">Token de actualizaci&oacute;n:&nbsp;<span id="current_refresh_token"></span></p>
		<p class="card-text">Atenci&oacute;n: El token de actualizaci&oacute;n es v&aacute;lido mientras la cuenta de correo mantenga la autorizaci&oacute;n de acceso mediante la cuenta de Google. Si cambia la clave de la cuenta de correo, se debe solicitar una nueva generaci&oacute;n de token.</p>
        <p class="card-text">V&aacute;lido hasta:&nbsp;<span id="current_refresh_token_expires"></span></p>
      </div>
    </div>
    </div>

    <!-- test email -->
    <div class="col-sm-12 col-md-6 mt-2 text-gray-600 dark:text-gray-400 text-sm">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Probar el env&iacute;o de correo</h5>
        <p class="card-text">Probar el env&iacute;o de correo usando la cuenta de Google y el token actual.</p>
        <form action="{{ route('send.email') }}" method="post">
        @csrf
        <input type="hidden" name="oauth_token" value="{{ session()->get('token') }}">
        <label for="email">Indique su direcci&oacute;n de correo electr&oacute;nico:</label><input required type="email" class="form-control" name="email">
        <button type="submit" class="btn btn-primary cursor mt-2 p-2 px-6 bg-gray-100 text-gray-700 font-semibold">Enviar Email de prueba</button>
        </form>
      </div>
    </div>
    </div>



    </div>
</div>
</body></html>