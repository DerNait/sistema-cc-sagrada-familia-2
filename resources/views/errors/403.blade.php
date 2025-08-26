@extends('errors.layout')

@section('title', '403 - Forbidden')
@section('logo', asset('images/logo_.jpg'))
@section('error_code', '403')
@section('error_message', 'Forbidden')
@section('button_text', 'Regresar')
@section('back_url', url()->previous() ?: url('/'))
@section('bg_image', asset('images/fondo_error.png'))
