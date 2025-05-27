@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <iframe
                        src="http://localhost:3000/public/dashboard/d15c163c-05fc-4a68-b358-d6a4ea2107b7"
                        frameborder="0"
                        width="800"
                        height="600"
                        allowtransparency
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
