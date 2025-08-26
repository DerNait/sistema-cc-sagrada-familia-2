@extends('errors.layout')

@section('title', '419- Page Expired')
@section('logo', asset('images/logo_.jpg'))
@section('error_code', '419')
@section('error_message', 'Page Expired')
@section('button_text', 'Regresar')
@section('back_url', url()->previous() ?: url('/'))
@section('bg_image', asset('images/fondo_error.png'))
