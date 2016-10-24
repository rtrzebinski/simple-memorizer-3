@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">

                @include('shared.manage_lesson')

                <div class="panel panel-default">
                    <div class="panel-heading">Manage exercises</div>
                    <div class="panel-body">
                        <div class="col-md-8 no-padding">
                            <p>
                                <a href="/exercises/create" class="btn btn-success margin-bottom" role="button">
                                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                    Create new exercise
                                </a>
                                <button class="btn btn-danger margin-bottom" data-title="Delete"
                                        data-toggle="modal" data-target="#delete">
                                    Delete selected exercises
                                    <span class="glyphicon glyphicon-trash"></span>
                                </button>
                            </p>
                        </div>
                        <div class="col-md-4 no-padding margin-bottom">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search for exercises...">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button">
                                        <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
                                    </button>
                                </span>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="table-responsive">
                            <table class="table table-bordred table-striped">
                                <thead>
                                <th><input type="checkbox" id="checkall"/></th>
                                <th>Question</th>
                                <th>Answer</th>
                                <th>Edit</th>
                                <th>Delete</th>
                                </thead>
                                <tbody>
                                @for ($i = 0; $i < 10; $i++)
                                    <tr>
                                        <td><input type="checkbox" class="checkthis"/></td>
                                        <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod
                                            tempor incididunt ut labore et dolore magna aliqua
                                        </td>
                                        <td> Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut
                                            aliquip ex ea commodo consequat.
                                        </td>
                                        <td>
                                            <a href="/exercises/edit" class="btn btn-info btn-xs">
                                                <span class="glyphicon glyphicon-pencil"></span>
                                            </a>
                                        </td>
                                        <td>
                                            <button class="btn btn-danger btn-xs" data-title="Delete"
                                                    data-toggle="modal" data-target="#delete">
                                                <span class="glyphicon glyphicon-trash"></span>
                                            </button>
                                        </td>
                                    </tr>
                                @endfor
                                </tbody>
                            </table>
                            <div class="clearfix"></div>
                            <ul class="pagination">
                                <li class="disabled">
                                    <a href="#">
                                        <span class="glyphicon glyphicon-chevron-left"></span>
                                    </a>
                                </li>
                                <li class="active"><a href="#">1</a></li>
                                <li><a href="#">2</a></li>
                                <li><a href="#">3</a></li>
                                <li><a href="#">4</a></li>
                                <li><a href="#">5</a></li>
                                <li>
                                    <a href="#">
                                        <span class="glyphicon glyphicon-chevron-right"></span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="delete" tabindex="-1" role="dialog" aria-labelledby="delete" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
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
                    <button type="button" class="btn btn-success">
                        <span class="glyphicon glyphicon-ok-sign"></span> Yes
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <span class="glyphicon glyphicon-remove"></span> No
                    </button>
                </div>
            </div>
        </div>
    </div>


@endsection