import {MESSAGE} from "../Constants/MessageConstants";
import ArrayFilters from "../Filters/ArrayFilters";
import Cache from "../../../../../../app/Resources/public/js/Helper/CacheHelper";
import youtubeMapper from "../Mapper/YoutubeMapper";
import AppHelperService from "../../../../../../app/Resources/public/js/Helper/AppHelperService";
import toastrService from "../../../../../../app/Resources/public/js/Services/ToastrService";

const YOUTUBE_PATTERN = [
    /(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/user\/\S+|\/ytscreeningroom\?v=|\/sandalsResorts#\w\/\w\/.*\/))([^\/&]{10,12})/,
    /(?:youtube\.com\/\S*(?:(?:\/e(?:mbed))?\/|watch\?(?:\S*?&?v\=))|youtu\.be\/)([a-zA-Z0-9_-]{6,11})/
];

export default (() => {
    var Public = {},
        Private = {};

    Private.mapper = youtubeMapper;
    Private.cacheKey = 'ads-youtube';
    Private.toastr = toastrService;

    Public.init = () => {
        Private.registerEvents();
        Private.reset();
    }

    Public.createVideo = (url = null) => {
        let youTubeId = url,
            match;

        if(!youTubeId) {
            youTubeId = $(Private.mapper.youtube).val();
        }

        if(AppHelperService.isUrl(youTubeId)){
            for(var i in YOUTUBE_PATTERN) {
                match = youTubeId.match(YOUTUBE_PATTERN[i]);

                if (match && match[1]) {
                    youTubeId = match[1];
                    break;
                }
            }
        }

        if (!youTubeId) {
            Private.toastr.warning(MESSAGE.ERROR.ID_REQUIRED);
        }
        else if(!Public.checkExistByProp('YouTubeId', youTubeId)) {
            Private.toastr.error(MESSAGE.ERROR.YOUTUBE_EXIST);
        }else {
            Private.getYouTube(youTubeId)
                .then(
                    function (response) {
                        var responseObj = Private.defaultObj();

                        response = response.items[0];

                        responseObj.YouTubeId = response.id;
                        responseObj.Title = response.snippet.title;
                        responseObj.ChannelId = response.snippet.channelId;
                        responseObj.ChanelTitle = response.snippet.channelTitle;
                        responseObj.Thumbnails = response.snippet.thumbnails;

                        Public.setHtml(responseObj);

                        Cache.add(Private.cacheKey, responseObj);
                    }
                )
                .fail(error => {
                    Private.toastr.error(MESSAGE.ERROR.YOUTUBE_URL_NOT_VALID);
                })
        }
    };

    Public.setHtml = function (response) {

        var iframe = tjq('<iframe>', {src: 'https://www.youtube.com/embed/'+ response.YouTubeId, frameborder: 0});

        $(Private.mapper.youtubeList).append(
            tjq('<li>', { 'class': 'col-md-6', 'data-id': response.YouTubeId })
                .append( tjq('<span>', {
                    class: 'youtube-close',
                }))
                .append(iframe)
        );
        $(Private.mapper.youtube).val("");

    };

    Public.setFromArray = data => {
        for(let i in data){
            Public.setHtml(data[i]);

            Cache.add(Private.cacheKey, data[i]);
        }
    };

    Public.getLists = function () {
        return Cache.get(Private.cacheKey);
    };

    /**
     * Remove youtube from array or
     * if is old add flag isDeleted
     * @param id
     * @return {boolean}
     */
    Public.removeFromLists = function (id) {
        const youtubeCache = Cache.get(Private.cacheKey);
        var filtered = ArrayFilters.getObjectByParams(youtubeCache, {name: 'YouTubeId', value: id}, true);

        if(filtered.length === 0)
            return false;

        if(filtered[0].data.Id) {
            youtubeCache[filtered[0].index].isDeleted = true;
            return true;
        }

        youtubeCache.splice(filtered[0].index, 1);
    };

    /**
     * Check if youtube is already exist
     * @param prop
     * @param value
     * @return {boolean}
     */
    Public.checkExistByProp = function (prop, value) {
        var selectedArray = ArrayFilters.getObjectByParams(Cache.get(Private.cacheKey), {name: prop, value: value});

        return selectedArray.length === 0
    }

    Private.defaultObj = function () {
        return {
            Id: null,
            AdsId: null,
            YouTubeId: null,
            Title: null,
            ChannelId: null,
            ChanelTitle: null,
            Thumbnails: null
        }
    };

    Private.getYouTube = function ($id) {
        return $.get(`https://www.googleapis.com/youtube/v3/videos?id=${$id}&key=${YOUTUBE_API_KEY}&fields=items(id,snippet,statistics,player)&part=snippet,statistics,player`);
    };

    Private.registerEvents = () => {
        $(document).on('click touchend', Private.mapper.youtubeButton, e => {
            Public.createVideo();
        });
        $(document).on('click touchend', Private.mapper.youtubeButtonClose, e => {
            let li = $(e.currentTarget).parent(),
                id = li.hasAndGetData('id');

            if(!id) {
                return false;
            }

            Public.removeFromLists(id);

            li.remove();
        });
    };

    Private.reset = function () {
        $(Private.mapper.youtubeList).empty();
        Cache.set(Private.cacheKey, []);
    };

    return Public;
});
