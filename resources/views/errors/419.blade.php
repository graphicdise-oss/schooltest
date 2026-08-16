@extends('errors.layout')

@section('icon', '⏳')
@section('code', 'ERROR 419')
@section('title', 'หมดเวลาการใช้งานหน้านี้')
@section('message', 'เซสชันของคุณหมดเวลาหรือหน้าถูกเปิดค้างไว้นานเกินไป กรุณาโหลดหน้าใหม่แล้วลองอีกครั้ง')
@section('button', 'โหลดหน้าใหม่')
@section('button_onclick', 'event.preventDefault(); window.location.reload();')
