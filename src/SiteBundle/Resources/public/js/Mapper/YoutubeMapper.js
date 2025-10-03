class YoutubeMapper {
    constructor() {
        if (!YoutubeMapper.instance) {
            this.youtubeList = '#youtube-video-lists';
            this.youtube = '#youtubeUrl';
            this.youtubeButton = '.youtube-add-btn';
            this.youtubeButtonClose = '.youtube-close';

            YoutubeMapper.instance = this;
        }

        return YoutubeMapper.instance;
    }
}
const youtubeMapper = new YoutubeMapper();

Object.freeze(youtubeMapper);

export default youtubeMapper;