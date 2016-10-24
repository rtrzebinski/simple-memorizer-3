@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">

                @include('shared.manage_lesson')

                <div class="panel panel-default">
                    <div class="panel-heading">Edit exercise</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-8 margin-bottom">
                                <form>
                                    <a href="/lessons/view" class="btn btn-default margin-bottom">
                                        <span class="glyphicon glyphicon-th" aria-hidden="true"></span>
                                        Browse exercises
                                    </a>
                                    <hr>
                                    <div class="form-group">
                                        <label>Question</label>
                                        <textarea class="form-control" rows="4"></textarea>
                                    </div>
                                    <div class="form-group has-error">
                                        <label>Correct answer</label>
                                        <textarea class="form-control" rows="4"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-default">Submit</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
