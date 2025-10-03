class SingleAdView
{

    constructor()
    {
        if(!SingleAdView.instance) {
            SingleAdView.instance = this;
        }

        return SingleAdView.instance;
    }

    generateView(alias, image, priceFrom, title, description, link)
    {
        let adminBtn = `
            <a class="button btn-small outline outline-orange change-ad" href="#" data-alias="${alias}">IZMENI</a>
            <a class="button btn-small remove-ad outline outline-red" href="#" data-alias="${alias}">OBRIŠI</a>
        `;

        if (0 === IS_ADVANCED_USER) {
            adminBtn = '';
        }

        let html = `<div class="col-sm-6 col-md-4">
                        <article class="box">
                            <figure class="img-290-160">
                                <a href="${link}" class="hover-effect" target="_blank">
                                    <img src="${image}" alt="${DEFAULT_ALT_TEXT}"
                                         title="${DEFAULT_TITLE_TEXT}" data-default-image="no">
                                </a>
                            </figure>
                            <div class="details">
                                            <span class="price">
                                                Od ${priceFrom}
                                            </span>
                                <h4 class="box-title"><span>${title}</span></h4>
                                <p class="text-middle description"><span>${description}</span></p>
                                <div class="action">
                                    <a class="button btn-small outline outline-dark-blue" href="${link}" target="_blank">POGLEDAJ</a>
                                    ${adminBtn}                                        
                                </div>
                            </div>
                        </article>
                    </div>`;

        return html;
    }
}
const singleAdView = new SingleAdView();

Object.freeze(singleAdView);

export default singleAdView;
