@extends('app.app')
@section('content')
    <div class="container">

        @if($all_albums)
            @if($admin_drag) @if($owner)
        <script type="text/javascript">
            $(document).ready(function(){
                Albums.Drag();
            });
        </script>@endif @endif
        <div class="buttonsprofile albumsbuttonsprofile" style="height:10px;">
            <div class="activetab"><a href="/albums/{{ $user_id }}/" onClick="Page.Go(this.href); return false;">
                    <div>@if(!$owner)Все альбомы {name}@endif @if($owner)Все альбомы@endif</div></a></div>
            @if($owner)<a href="" onClick="Albums.CreatAlbum(); return false;">Создать альбом</a>@endif
            <a href="/albums/comments/{{ $user_id }}/" onClick="Page.Go(this.href); return false;">Комментарии к альбомам</a>
            @if(!$owner)<a href="/u{{ $user_id }}" onClick="Page.Go(this.href); return false;">К странице {{ $name }}</a>@endif
            @if($new_photos)<a href="/albums/newphotos/" onClick="Page.Go(this.href); return false;">Новые фотографии со мной (<b>{{ $num }}</b>)</a>@endif
        </div>
        <div class="clear"></div>
        @elseif($view)
        <input type="hidden" id="all_p_num" value="{all_p_num}" />
        <input type="hidden" id="aid" value="{aid}" />
        <div class="buttonsprofile albumsbuttonsprofile" style="height:10px;">
            <a href="/albums/{{ $user_id }}/" onClick="Page.Go(this.href); return false;">[not-owner]Все альбомы {name}[/not-owner][owner]Все альбомы[/owner]</a>
            <div class="activetab"><a href="/albums/view/{aid}/" onClick="Page.Go(this.href); return false;"><div>{album-name}</div></a></div>
            <a href="/albums/view/{aid}/comments/" onClick="Page.Go(this.href); return false;">Комментарии к альбому</a>
            [owner]<a href="/albums/edit/{aid}/" onClick="Page.Go(this.href); return false;">Изменить порядок фотографий</a>
            <a href="/albums/add/{aid}/" onClick="Page.Go(this.href); return false;">Добавить фотографии</a>[/owner]
            [not-owner]<a href="/u{{ $user_id }}" onClick="Page.Go(this.href); return false;">К странице {name}</a>[/not-owner]
        </div>
        <div class="clear"></div><div style="margin-top:8px;"></div>
        @elseif($editphotos)
        [admin-drag]<script type="text/javascript">
            $(document).ready(function(){
                Photo.Drag();
            });
        </script>[/admin-drag]
        <script type="text/javascript" src="/js/albums.view.js"></script>
        <input type="hidden" id="all_p_num" value="{all_p_num}" />
        <input type="hidden" id="aid" value="{aid}" />
        <div class="buttonsprofile albumsbuttonsprofile" style="height:10px;">
            <a href="/albums/{{ $user_id }}/" onClick="Page.Go(this.href); return false;">Все альбомы</a>
            <a href="/albums/view/{aid}/" onClick="Page.Go(this.href); return false;">{album-name}</a>
            <a href="/albums/view/{aid}/comments/" onClick="Page.Go(this.href); return false;">Комментарии к альбому</a>
            <div class="activetab"><a href="/albums/edit/{aid}/" onClick="Page.Go(this.href); return false;"><div>Изменить порядок фотографий</div></a></div>
            <a href="/albums/add/{aid}/" onClick="Page.Go(this.href); return false;">Добавить фотографии</a>
        </div>
        <div class="clear"></div><div style="margin-top:8px;"></div>
        @elseif($comments)
        <script type="text/javascript" src="/js/albums.view.js"></script>
        <div class="buttonsprofile albumsbuttonsprofile">
            <a href="/albums/{{ $user_id }}/" onClick="Page.Go(this.href); return false;">[not-owner]Все альбомы {name}[/not-owner][owner]Все альбомы[/owner]</a>
            [owner]<a href="" onClick="Albums.CreatAlbum(); return false;">Создать альбом</a>[/owner]
            <div class="activetab"><a href="/albums/comments/{{ $user_id }}/" onClick="Page.Go(this.href); return false;"><div>Комментарии к альбомам</div></a></div>
            [not-owner]<a href="/u{{ $user_id }}" onClick="Page.Go(this.href); return false;">К странице {name}</a>[/not-owner]
        </div>
        <div class="clear"></div>
        @elseif($albums_comments)
        <script type="text/javascript" src="/js/albums.view.js"></script>
        <div class="buttonsprofile albumsbuttonsprofile">
            <a href="/albums/{{ $user_id }}/" onClick="Page.Go(this.href); return false;">[not-owner]Все альбомы {name}[/not-owner][owner]Все альбомы[/owner]</a>
            <a href="/albums/view/{aid}/" onClick="Page.Go(this.href); return false;">{album-name}</a>
            <div class="activetab"><a href="/albums/view/{aid}/comments/" onClick="Page.Go(this.href); return false;"><div>Комментарии к альбому</div></a></div>
            [owner]<a href="/albums/edit/{aid}/" onClick="Page.Go(this.href); return false;">Изменить порядок фотографий</a>
            <a href="/albums/add/{aid}/" onClick="Page.Go(this.href); return false;">Добавить фотографии</a>[/owner]
            [not-owner]<a href="/u{{ $user_id }}" onClick="Page.Go(this.href); return false;">К странице {name}</a>[/not-owner]
        </div>
        <div class="clear"></div>
        @elseif($all_photos)
        <script type="text/javascript" src="/js/albums.view.js"></script>
        <div class="buttonsprofile albumsbuttonsprofile" style="height:10px;">
            <a href="/albums/{{ $user_id }}/" onClick="Page.Go(this.href); return false;">[not-owner]Все альбомы {name}[/not-owner][owner]Все альбомы[/owner]</a>
            [owner]<a href="" onClick="Albums.CreatAlbum(); return false;">Создать альбом</a>[/owner]
            <a href="/albums/comments/{{ $user_id }}/" onClick="Page.Go(this.href); return false;">Комментарии к альбомам</a>
            <div class="activetab"><a href="/photos/{{ $user_id }}/" onClick="Page.Go(this.href); return false;"><div>Обзор фотографий</div></a></div>
            [not-owner]<a href="/u{{ $user_id }}" onClick="Page.Go(this.href); return false;">К странице {name}</a>[/not-owner]
        </div>
        <div class="clear"></div><div style="margin-top:8px;"></div>
        @endif

            <div id="dragndrop">
                <ul>
                    @if($albums)
                        @foreach($albums as $row)
                            <div id="album_{aid}" class="albums" [owner]style="cursor:move"[/owner]>
                            <div class="hralbum"></div>
                            <a href="/albums/view/{aid}/" onClick="Page.Go(this.href); return false"><div class="albums_cover"><span id="cover_{aid}"><img src="{cover}" alt="" /></span></div></a>
                            <div class="albums_name"><a href="/albums/view/{aid}/" onClick="Page.Go(this.href); return false" id="albums_name_{aid}">{name}</a></div>
                            <div class="albums_photo_num">{photo-num}, {comm-num}</div>
                            <div class="albums_photo_num">Обновлён {date}</div>
                            <div class="album_desc"><span id="descr_{aid}">{descr}</span></div>
                            [owner]<div class="infowalltext albums_infowalltext">
                                <a href="/" onClick="Albums.EditBox({aid}); return false">Редактировать</a>&nbsp; | &nbsp;
                                <a href="/" onClick="Albums.EditCover({aid}); return false">Изменить обложку</a>&nbsp; | &nbsp;
                                <a href="/albums/add/{aid}" onClick="Page.Go(this.href); return false">Добавить фотографии</a>&nbsp; | &nbsp;
                                <a href="/" onClick="Albums.Delete({aid}, '{hash}'); return false">Удалить</a>
                            </div>[/owner]
                            <div class="clear"></div>
                            </div>
                        @endforeach
                    @else
                        <br /><br />Пока нет ни одного фотоальбома.<br /><br /><br />'
                    @endif
                </ul>
            </div>

    </div>

@endsection