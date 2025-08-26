@extends('errors.layout')

@section('title', '401 - Unauthorized')
@section('logo', asset('images/logo_.jpg'))
@section('error_code', '401')
@section('error_message', 'Unauthorized')
@section('button_text', 'Regresar')
@section('back_url', url()->previous() ?: url('/'))
@section('bg_image', asset('images/fondo_error.png'))
