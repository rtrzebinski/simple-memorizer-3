@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">

                @include('shared.manage_lesson')

                <div class="panel panel-default">
                    <div class="panel-heading">Exercises</div>
                    <div class="panel-body">
                        <div class="col-md-8 no-padding">
                            @can('modify', $lesson)
                                <p>
                                    <a href="/lessons/{{ $lesson->id }}/exercises/create"
                                       class="btn btn-success margin-bottom" role="button">
                                        <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                        Add exercise
                                    </a>
                                    <button class="btn btn-danger margin-bottom" data-title="Delete"
                                            data-toggle="modal" data-target="#delete">
                                        <span class="glyphicon glyphicon-trash"></span>
                                        Delete selected
                                    </button>

                                    @can('modify', $lesson)
                                        <a href="" class="btn btn-default margin-bottom">
                                            <span class="glyphicon glyphicon-import" aria-hidden="true"></span>
                                            Import from CSV
                                        </a>
                                    @endcan

                                    <a href="/lessons/{{ $lesson->id }}/csv" class="btn btn-default margin-bottom">
                                        <span class="glyphicon glyphicon-export" aria-hidden="true"></span>
                                        Export to CSV
                                    </a>

                                </p>
                            @endcan
                        </div>
                        <div class="clearfix"></div>
                        <div class="table-responsive">
                            <table class="table table-bordred table-striped">
                                <thead>
                                @can('modify', $lesson)
                                    <th><input type="checkbox" id="checkall"/></th>
                                @endcan
                                <th>Question</th>
                                <th>Answer</th>
                                <th><span class="glyphicon glyphicon-education" aria-hidden="true"></span> %</th>
                                @can('modify', $lesson)
                                    <th>Edit</th>
                                    <th>Delete</th>
                                @endcan
                                </thead>
                                <tbody>
                                @foreach($exercises as $row)
                                    <tr>
                                        @can('modify', $lesson)
                                            <td>
                                                <input type="checkbox" class="checkthis"/>
                                            </td>
                                        @endcan
                                        <td>
                                            {{ $row->question }}
                                        </td>
                                        <td>
                                            {{ $row->answer }}
                                        </td>
                                        <td>
                                            {{ $row->percent_of_good_answers }}
                                        </td>
                                        @can('modify', $lesson)
                                            <td>
                                                <a href="/exercises/{{ $row->id }}/edit" class="btn btn-info btn-xs">
                                                    <span class="glyphicon glyphicon-pencil"></span>
                                                </a>
                                            </td>
                                            <td>
                                                <button class="btn btn-danger btn-xs" data-title="Delete"
                                                        data-toggle="modal" data-target="#delete-exercise-{{ $row->id }}">
                                                    <span class="glyphicon glyphicon-trash"></span>
                                                </button>
                                            </td>
                                        @endcan
                                    </tr>
                                    <div class="modal fade" id="delete-exercise-{{ $row->id }}" tabindex="-1"
                                         role="dialog"
                                         aria-labelledby="delete" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            aria-hidden="true">
                                                        <span class="glyphicon glyphicon-remove"
                                                              aria-hidden="true"></span>
                                                    </button>
                                                    <h4 class="modal-title custom_align">Delete</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="alert alert-danger">
                                                        <span class="glyphicon glyphicon-warning-sign"></span>
                                                        Are you sure you want to delete?
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" form="delete-exercise-form-{{ $row->id }}" class="btn btn-success">
                                                        <span class="glyphicon glyphicon-ok-sign"></span> Yes
                                                    </button>
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">
                                                        <span class="glyphicon glyphicon-remove"></span> No
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <form id="delete-exercise-form-{{ $row->id }}" action="/exercises/{{ $row->id }}" method="POST">
                                            <input name="_method" type="hidden" value="DELETE">
                                            {{ csrf_field() }}
                                        </form>
                                    </div>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection