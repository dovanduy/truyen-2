@extends('vendor.installer.layouts.master')

@section('title', trans('installer_messages.final.title'))
@section('container')
    <p class="paragraph" style="text-align: center;">{{ session('message')['message'] }}</p>
    <div class="bg-primary">
        <p>User Name: admin</p>
        <p>Password: admin.password</p>
    </div>
    <div class="buttons">
        <a href="{{ url('/admin') }}" class="button">{{ trans('installer_messages.final.exit') }}</a>
    </div>
@stop
