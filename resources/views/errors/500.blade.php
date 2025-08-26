@extends('errors.layout')

@section('title', '500 - Server Error')
@section('logo', asset('images/logo_.jpg'))
@section('error_code', '500')
@section('error_message', 'Server Error')
@section('button_text', 'Regresar')
@section('back_url', url()->previous() ?: url('/'))
@section('bg_image', asset('images/fondo_error.png'))
