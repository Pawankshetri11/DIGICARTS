DIGICARTS HEADER & FOOTER 1.8.0

Separate header and footer plugin for the DIGICARTS digital products theme.

ADMIN CUSTOMIZATION
Open Header & Footer in the WordPress admin sidebar.
Edit the logo, brand name, colors, content width, announcement, search labels,
account/cart labels, category navigation label, promotion, five service benefits,
contact details, footer headings, copyright, newsletter and payment image.
Manage navigation links and dropdowns under Appearance > Menus and assign their locations.
Product search categories come from Products > Categories.
Wishlist is disabled by default. Enable Show wishlist in the header settings to restore it. With no custom wishlist URL, products can be saved from search results and WooCommerce product pages to a wishlist stored in the visitor browser. A configured wishlist URL connects an external wishlist instead.
The top offer banner is disabled by default. It can be restored with Show dismissible discount banner in the header settings. Edit its message and link in Header & Footer > Header. Closing it hides that message for the current browser session; changing the message/link makes it appear again.
Upload payment logos only for the methods your store offers.

NEWSLETTER
The built-in form records consented email addresses locally under Header & Footer > Subscribers.
It does not send confirmation emails or campaigns.
To use a mailing service, put its newsletter form shortcode in the newsletter settings.
Administrators can remove subscriber records from the Subscribers screen.

INSTALLATION AND INTEGRATION
Upload digicarts-header-footer.zip under Plugins > Add New > Upload Plugin and activate.
DIGICARTS theme 1.1.0 includes the digicarts_header and digicarts_footer action hooks.
The theme owns the HTML document shell, wp_head, wp_body_open and wp_footer calls.
Other themes need those integration hooks, or the [digicarts_header] and [digicarts_footer] shortcodes.
Do not add both hooks and shortcodes in the same layout, which would duplicate the components.
WooCommerce supplies product categories, account links and live cart totals when active.
Deactivation preserves settings, menus and subscriber records.

No automated tests or browser checks were run for this release, at the user's request.

DESIGN
Readable navigation, neutral surfaces with violet/lavender accents matching the original tilted four-tile logo, simple outline SVG icons and a full-width newsletter section. Controls use gentle 6–8px corner rounding with larger text and spacing.
Poppins weights 400, 500, 600 and 700 are bundled locally under assets/fonts with their OFL license.
No external font service is needed. Colors and labels remain editable in admin.

LIVE STOREFRONT
The header stays sticky and accounts for the WordPress admin toolbar.
Product search uses a read-only AJAX endpoint after two characters, supports the category filter, and returns up to six published, search-visible products. Enter also loads suggestions without reloading. View all search results opens the standard results page.
The built-in wishlist stores up to 100 products locally; it does not synchronize across browsers or accounts.
No tests or browser checks were run for these changes, as requested.

The search category selector uses a styled dropdown with a selected checkmark, arrow-key navigation, type-ahead, Escape and outside-click dismissal. The native select remains as the no-JavaScript fallback and supplies the category filter for AJAX search.

FOOTER REDESIGN
A lavender newsletter section sits above the brand, product collections and two editable navigation columns. Benefits appear below these links, followed by copyright, optional payment methods and a floating Back to top icon. Contact details and all existing newsletter integrations are retained. The collections heading is editable; its links come from Products > Categories.
Logged-in visitors see the account icon and Dashboard in the header, linking to their WooCommerce account. Logged-out visitors keep the login/account treatment.
The homepage content remains empty. No tests or browser checks were run.

The newsletter section is disabled by default and on this local site. Footer policy links use Appearance > Menus > DIGICARTS footer policies. Privacy, terms and refund pages currently contain editable placeholders; existing draft policies were preserved.

SOCIALS AND CHECKOUT
A divider separates the footer content from the legal row. Instagram, Facebook, YouTube and X icons appear below the brand; empty profile URLs render non-clickable placeholders. Set profile URLs under Header & Footer > Footer & contact details.
Simple checkout shows bundled SVG marks for UPI, Visa, RuPay and Mastercard below the account links. Its heading and visibility are editable, and the payment-image upload can replace the bundled marks. These are display assets; payment gateways remain configured in WooCommerce.
The benefits strip and newsletter remain disabled. No tests or browser checks were run.

The header coupon highlight uses a red flower-shaped percent badge and a soft red background. Edit Header coupon text and Coupon / offer link under Header & Footer > Header. This displays a message; actual coupons are configured in WooCommerce.

SIDE CART
Header and store cart links open a right-side cart drawer. Direct GET requests to the WooCommerce Cart page redirect to the shop with the drawer open. The WooCommerce Cart page assignment remains intact for plugin compatibility.
The drawer uses the native WooCommerce Store API and its session nonce checks. It supports quantity changes subject to stock and product limits, product removal, coupon application/removal, server-calculated taxes/fees/discounts/totals, shipping estimates and shipping selection when required, and a link to checkout. Checkout and payment still happen on the checkout page.
Classic and block Add to cart events open the drawer; regular product-form submissions reopen it after navigation. Drawer updates refresh the header cart and invalidate the WooCommerce block cart cache. No live orders, coupon attempts, tests or browser checks were run during implementation, at the user's request.
