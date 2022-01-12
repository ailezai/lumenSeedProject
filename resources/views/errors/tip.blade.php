@extends('layouts.app')

@section('title', '提示')

@section('content')
    <div class="wrapper wrapper-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-danger">
                    <div class="panel-heading">
                        <h3 class="panel-title">提示</h3>
                    </div>
                    <div class="panel-body">
                        <p>{{ $msg }}</p>
                        <button type="button" class="btn btn-default" onclick="window.history.go(-1);">
                            <i class="fa fa-reply fa-fw"></i>返回
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop