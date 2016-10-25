@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">

                {{--<div class="panel panel-default">--}}
                {{--<div class="panel-body">--}}
                {{--<div class="row">--}}
                {{--<div class="col-xs-12 col-sm-8">--}}
                {{--<h2>Mike Anamendolla</h2>--}}
                {{--<p><strong>About: </strong> Web Designer / UI. </p>--}}
                {{--<p><strong>Hobbies: </strong> Read, out with friends, listen to music, draw and learn--}}
                {{--new things. </p>--}}
                {{--<p><strong>Skills: </strong>--}}
                {{--<span class="label label-info tags">html5</span>--}}
                {{--<span class="label label-info tags">css3</span>--}}
                {{--<span class="label label-info tags">jquery</span>--}}
                {{--<span class="label label-info tags">bootstrap3</span>--}}
                {{--</p>--}}
                {{--</div><!--/col-->--}}
                {{--<div class="col-xs-12 col-sm-4 text-center">--}}
                {{--<img src="http://api.randomuser.me/portraits/men/49.jpg" alt=""--}}
                {{--class="center-block img-circle img-responsive">--}}
                {{--<ul class="list-inline ratings text-center" title="Ratings">--}}
                {{--<li><a href="#"><span class="fa fa-star fa-lg"></span></a></li>--}}
                {{--<li><a href="#"><span class="fa fa-star fa-lg"></span></a></li>--}}
                {{--<li><a href="#"><span class="fa fa-star fa-lg"></span></a></li>--}}
                {{--<li><a href="#"><span class="fa fa-star fa-lg"></span></a></li>--}}
                {{--<li><a href="#"><span class="fa fa-star fa-lg"></span></a></li>--}}
                {{--</ul>--}}
                {{--</div><!--/col-->--}}

                {{--<div class="col-xs-12 col-sm-4">--}}
                {{--<h2><strong> 20,7K </strong></h2>--}}
                {{--<p>--}}
                {{--<small>Subscribers</small>--}}
                {{--</p>--}}
                {{--<button class="btn btn-success btn-block"><span class="fa fa-plus-circle"></span>--}}
                {{--Subscribe--}}
                {{--</button>--}}
                {{--</div><!--/col-->--}}
                {{--<div class="col-xs-12 col-sm-4">--}}
                {{--<h2><strong>245</strong></h2>--}}
                {{--<p>--}}
                {{--<small>Subscribers</small>--}}
                {{--</p>--}}
                {{--<button class="btn btn-info btn-block"><span class="fa fa-user"></span> View Profile--}}
                {{--</button>--}}
                {{--</div><!--/col-->--}}
                {{--<div class="col-xs-12 col-sm-4">--}}
                {{--<h2><strong>43</strong></h2>--}}
                {{--<p>--}}
                {{--<small>Snippets</small>--}}
                {{--</p>--}}
                {{--<button type="button" class="btn btn-primary btn-block"><span class="fa fa-gear"></span>--}}
                {{--Options--}}
                {{--</button>--}}
                {{--</div><!--/col-->--}}
                {{--</div><!--/row-->--}}
                {{--</div><!--/panel-body-->--}}
                {{--</div><!--/panel-->--}}

                @if($userHasOwnedOrSubscribedLessons)
                    <div class="panel panel-default">
                        <div class="panel-heading">Your lessons</div>
                        <div class="panel-body">
                            <div class="col-md-8 no-padding">
                                <p class="lead">
                                    Lessons created by you and lesson you subscribe.</br>
                                    Start learning right now!</p>
                                <p>
                                    <a href="/lessons/create" class="btn btn-success" role="button">
                                        <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                        Create new lesson
                                    </a>
                                </p>
                            </div>
                            <div class="col-md-4 no-padding">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search your lessons...">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button">
                                        <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
                                    </button>
                                </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        @if(isset($ownedLessons) && !empty($ownedLessons))
                            @foreach($ownedLessons as $row)
                                <div class="col-sm-6 col-md-4">
                                    <div class="thumbnail">
                                        <div class="caption">
                                            <h4>
                                                <span class="glyphicon glyphicon-education" aria-hidden="true"></span>
                                                {{ str_limit($row->name, 30) }}
                                            </h4>
                                            <p>
                                                Number of exercises: 40 </br>
                                                Number of subscribers: 3 </br>
                                            </p>
                                            <p>
                                                <a href="/lessons/{{ $row->id }}/learn" class="btn btn-primary"
                                                   role="button">
                                                    <span class="glyphicon glyphicon-play" aria-hidden="true"></span>
                                                    Start learning
                                                </a>
                                                <a href="/lessons/{{ $row->id }}" class="btn btn-default"
                                                   role="button">
                                                    <span class="glyphicon glyphicon-list" aria-hidden="true"></span>
                                                    Manage
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        @if(isset($subscribedLessons) && !empty($subscribedLessons))
                            @foreach($subscribedLessons as $row)
                                <div class="col-sm-6 col-md-4">
                                    <div class="thumbnail">
                                        <div class="caption">
                                            <h4>
                                                <span class="glyphicon glyphicon-education" aria-hidden="true"></span>
                                                {{ str_limit($row->name, 30) }}
                                            </h4>
                                            <p>
                                                Number of exercises: 40 </br>
                                                Number of subscribers: 3 </br>
                                            </p>
                                            <p>
                                                <a href="/lessons/{{ $row->id }}/learn" class="btn btn-primary"
                                                   role="button">
                                                    <span class="glyphicon glyphicon-play" aria-hidden="true"></span>
                                                    Start learning
                                                </a>
                                                <a href="#" class="btn btn-danger" role="button">
                                                    <span class="glyphicon glyphicon-remove-sign"
                                                          aria-hidden="true"></span>
                                                    Unsubscribe
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endif

                <div class="panel panel-default">
                    <div class="panel-heading">Available lessons</div>
                    <div class="panel-body">
                        <div class="col-md-8 no-padding">
                            <p class="lead">
                                Browse and subscribe lessons created by others users. </br>
                                You can start learning a lesson right after you subscribe it.
                            </p>
                            <p>
                                @if(!$userHasOwnedOrSubscribedLessons)
                                    <a href="/lessons/create" class="btn btn-success" role="button">
                                        <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                        Create your own lesson
                                    </a>
                                @endif
                                <a href="#" class="btn btn-default" role="button">
                                    <span class="glyphicon glyphicon-book" aria-hidden="true"></span>
                                    Browse all available lessons
                                </a>
                            </p>
                        </div>
                        <div class="col-md-4 no-padding">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search available lessons...">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button">
                                        <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    @if(isset($availableLessons) && !empty($availableLessons))
                        @foreach($availableLessons as $row)
                            <div class="col-sm-6 col-md-4">
                                <div class="thumbnail">
                                    <div class="caption">
                                        <h4>
                                            <span class="glyphicon glyphicon-education" aria-hidden="true"></span>
                                            {{ str_limit($row->name, 30) }}
                                        </h4>
                                        <p>
                                            Number of exercises: 40 </br>
                                            Number of subscribers: 3 </br>
                                        </p>
                                        <p>
                                            <a href="#" class="btn btn-primary" role="button">
                                                <span class="glyphicon glyphicon-ok-circle" aria-hidden="true"></span>
                                                Subscribe
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

            </div>
        </div>
    </div>
@endsection
