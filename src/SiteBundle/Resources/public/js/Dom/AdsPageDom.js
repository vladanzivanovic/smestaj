import adsPageMapper from "../Mapper/AdsPageMapper";
import MobileDetection from "../MobileDetection";

class AdsPageDom {
    constructor() {
        this.mapper = adsPageMapper;
        this.md = MobileDetection;
    }

    addCriteriaOnPage(name, value, text) {

        const criteria = `<a class="btn selected-filter-btn letter-capitalize" data-name="${name}" data-value="${value}">${text}
                            <span class="close"></span>
                          </a>`;

        $(this.mapper.searchView).append(criteria);
    }

    generateProducts(data) {
        let html = '';

        for(let i in data.ads.data) {
            let ad = data.ads.data[i];
            let imageHtml = '';
            const adLink = Routing.generate(`site_ads_view`, {'category': ad.ad_categories.main.slug, 'extraParams': `${ad.address.city.slug}/${ad.slug}`});

            let payedAdClass = ad.isPayed == 1 ? 'payed-product' : '';
            let tagsHtml = '';

            for (const tag of ad.tags) {
                tagsHtml += `
                    <li class="ad-tag-icon" title="${tag.title}">
                        <i class="${tag.icon} circle"></i>
                    </li>
                `;
            }

            if (ad.media.images.main) {
                imageHtml = `<img src="${ad.media.images.main._links.list_thumb}" alt="${ad.title}" title="${ad.title}">`;
            }

            html += `<div class="product-grid">
                        <article class="box ${payedAdClass}">
                            <figure>
                                <a title="${ad.title}" href="${adLink}" class="hover-effect">
                                    ${imageHtml}
                                </a>
                            </figure>
                            <div class="details">
                                <span class="price pad-r-1"><small>Već od:</small>${ad.prices.price_pre_season.from}</span>
                                <h4 class="box-title pad-l-1">${ad.title}<small>${ad.address.city.title}</small></h4>
                                <div class="amenities">
                                    <ul class="pad-side-1">
                                        ${tagsHtml}
                                    </ul>
                                </div>
                                <p class="mile pad-side-1">${ad.ad_categories.main.title}</p>
                                <div class="action">
                                    <a class="button btn-small full-width dark-blue1" href="${adLink}" title="${ad.title}">POGLEDAJ</a>
                                </div>
                            </div>
                        </article>
                    </div>`;
        }

        return html;
    };
}

const adsPageDom = new AdsPageDom();

Object.freeze(adsPageDom);

export default adsPageDom;
