@extends('errors.layout')

@section('title', '404 - Not Found')
@section('logo', asset('images/logo_.jpg'))
@section('error_code', '404')
@section('error_message', 'Not Found')
@section('button_text', 'Regresar')
@section('back_url', url()->previous() ?: url('/'))
@section('bg_image', asset('images/fondo_error.png'))
