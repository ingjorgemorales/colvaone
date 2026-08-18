@include('errors.partials.shell', [
    'code' => 403,
    'title' => 'Acceso no autorizado',
    'message' => 'Tu usuario no tiene permisos para entrar a esta opcion. Si necesitas acceso, solicita que revisen tu rol o permisos.',
])
