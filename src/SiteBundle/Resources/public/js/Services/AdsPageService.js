import adsPageDom from "../Dom/AdsPageDom";
import adsPageMapper from "../Mapper/AdsPageMapper";
import PaginationDom from "../Dom/PaginationDom";
import Loader from "../../../../../../app/Resources/public/js/Dom/Loader";
import Tipped from "@staaky/tipped";

require('jquery.scrollto');

class AdsPageService {
    constructor() {
        this.mapper = adsPageMapper;
        this.dom = adsPageDom;
        this.pagination = new PaginationDom(this.mapper);
    }

    applyFilter(url)
    {
        Loader.pageLoaderToggle();
        $.ajax({
            type: 'GET',
            url: url,
            dataType: 'json',
            success: response => {
                $(this.mapper.listItems).empty()
                    .append(this.dom.generateProducts(response));

                $.scrollTo($(this.mapper.listItems), {
                    duration: 1000,
                    interrupt: true,
                    over:{left:0, top: -0.1},
                })

                Loader.pageLoaderToggle();

                this.pagination.generate(response.ads.pagination);
                $(this.mapper.totalProductText).text(Translator.transChoice('shop.total_products', response.ads.pagination.totalRows, null, 'messages', LOCALE));

                Tipped.create('.ad-tag-icon');

            },
            error: error => {

            }
        })
    }
}

export default AdsPageService;
