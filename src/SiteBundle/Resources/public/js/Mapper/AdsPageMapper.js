class AdsPageMapper {
    constructor() {

        if (!AdsPageMapper.instance) {
            this.searchView = '.search-criteria';
            this.filterOptions = '.filters-option';
            this.tags = '.tag-btn';
            this.listItems = '.item-list';
            this.totalProductText = '#total-product-text';
            this.cityList = '#city';
            this.sortList = '#sort-list';
            this.filterBtnOpen = '#filter-btn-open';
            this.filterBtnClose = '#filter-btn-close';

            AdsPageMapper.instance = this;
        }

        return AdsPageMapper.instance;
    }
}

const adsPageMapper = new AdsPageMapper();

Object.freeze(adsPageMapper);

export default adsPageMapper;
