
# CartThrob 2 PayPoint.net (Secpay) payment gateway

This add-on allows you to take payments with PayPoint's [Lite and Professional gateways][3].

**Please note this plugin requires CartThrob 2.0503**

## Contents

*   **[Installation &amp; configuration][6]**
*   [**Checkout form field names**][7]
*   **[Response template][8]**

## Installation &amp; Configuration

### System requirements

Tested with:

*   EE2.2.2
*   CarThrob 2.0503

### <a id="installation">Installing

1.  Download and extract the zip archive
2.  Place the file *Cartthrob\_paypoint\_secpay.php* in your cartthrob/payment_gateways folder
3.  Place the file *cartthrob\_paypoint\_secpay_lang.php* in your cartthrob/language/english folder. Feel free to translate and send back to me.
4.  Make a Response template (see [example code below][8]).

### Configuring within CartThrob

1.  Login to EE and go to your CartThrob control panel
2.  Click the Payments tab
3.  Select **PayPoint.net (Secpay)** from **Choose your primary payment gateway**
4.  Enter your **Merchant ID**
5.  Enter your **digest password**
6.  Select a **Transaction Mode**. This will need to be **Live** to take actual orders.
7.  Enter your [**currency code**][14]
8.  If you have a pro account, enter the name of your **Payment template**
9.  Select your **Response template**
10. Specify if PayPoint should send it's own customer notification emails with **Send Paypoint customer email**
11. Specify if PayPoint should send it's own vendor notification emails with **Send Paypoint admin email**
12. Specify if cart contents should be sent to PayPoint with **Send order items**
13. Now configure your shop and templates as per standard [CartThrob instructions][15]

## <a id="variables"/>Checkout form field names

The checkout form field names for CartThrob and PayPoint differ, but (from the point of view of this plugin) you can use either. It's probably best to use the CartThrob names however to maximise compatibility.

Please note that if you submit a PayPoint field name to the checkout, it will overwrite the CartThrob equivalent. The table below contains a full list of supported field names.

<table width="90%" border="1">

  <tr>

    <th scope="col">PayPoint field name</th>

    <th scope="col">CartThrob field name</th>

    <th width="30%" scope="col">Notes</th>

  </tr>

  <tr>

    <td>bill_addr_1</td>

    <td>address</td>

    <td>&nbsp;</td>

  </tr>

  <tr>

    <td>bill_addr_2</td>

    <td>address2</td>

    <td>&nbsp;</td>

  </tr>

  <tr>

    <td>bill_city</td>

    <td>city</td>

    <td>&nbsp;</td>

  </tr>

  <tr>

    <td>bill_company</td>

    <td>company</td>

    <td>&nbsp;</td>

  </tr>

  <tr>

    <td>bill_country</td>

    <td>country</td>

    <td>&nbsp;</td>

  </tr>

  <tr>

    <td>bill_email</td>

    <td>email_address</td>

    <td>&nbsp;</td>

  </tr>

  <tr>

    <td>bill_fax</td>

    <td class="na">n/a</td>

    <td class="na">&nbsp;</td>

  </tr>

  <tr>

    <td>bill_name</td>

    <td class="na">n/a</td>

    <td><em>If not provided, will be automatically generated from first and last name</em></td>

  </tr>

  <tr>

    <td>bill_post_code</td>

    <td>zip</td>

    <td>&nbsp;</td>

  </tr>

  <tr>

    <td>bill_state</td>

    <td>state</td>

    <td>&nbsp;</td>

  </tr>

  <tr>

    <td height="32">bill_tel</td>

    <td>phone</td>

    <td>&nbsp;</td>

  </tr>

  <tr>

    <td>bill_url</td>

    <td class="na">n/a</td>

    <td class="na">&nbsp;</td>

  </tr>

  <tr>

    <td class="na">n/a</td>

    <td>last_name           </td>

    <td>&nbsp;</td>

  </tr>

  <tr>

    <td>ship_addr_1</td>

    <td>shipping_address </td>

    <td>&nbsp;</td>

  </tr>

  <tr>

    <td>ship_addr_2</td>

    <td>shipping_address2</td>

    <td>&nbsp;</td>

  </tr>

  <tr>

    <td>ship_city</td>

    <td>shipping_city      </td>

    <td>&nbsp;</td>

  </tr>

  <tr>

    <td>ship_company</td>

    <td>shipping_company</td>

    <td>&nbsp;</td>

  </tr>

  <tr>

    <td>ship_country</td>

    <td>shipping_country</td>

    <td>&nbsp;</td>

  </tr>

  <tr>

    <td>ship_email</td>

    <td class="na">n/a</td>

    <td class="na">&nbsp;</td>

  </tr>

  <tr>

    <td>ship_fax</td>

    <td class="na">n/a</td>

    <td class="na">&nbsp;</td>

  </tr>

  <tr>

    <td>ship_name</td>

    <td class="na">n/a</td>

    <td><em>If not provided, will be automatically generated from first and last name</em></td>

  </tr>

  <tr>

    <td>ship_post_code</td>

    <td>shipping_zip  </td>

    <td>&nbsp;</td>

  </tr>

  <tr>

    <td>ship_state</td>

    <td>shipping_state    </td>

    <td>&nbsp;</td>

  </tr>

  <tr>

    <td>ship_tel</td>

    <td class="na">n/a</td>

    <td class="na">&nbsp;</td>

  </tr>

  <tr>

    <td>ship_url</td>

    <td class="na">n/a</td>

    <td class="na">&nbsp;</td>

  </tr>

  <tr>

    <td class="na">n/a</td>

    <td>shipping_last_name  </td>

    <td>&nbsp;</td>

  </tr>

  </table>



## <a id="response"/>Response template

This is a sample template. Available available variables are:

    {if authorized}...{/if}
    {if not_authorized}...{/if}
    {order_id}
    {error_message}


    {if authorized}
        # Thank you for your order

        Your order has been processed and a confirmation email has been sent to you.

        Your order number is: **{order_id}**, please quote this is any correspondance with us.


    {/if}

    {if not_authorized}

        # Order Failure

        {error_message}

        **YOUR CREDIT CARD WAS NOT AUTHORISED.**

        Your account will not be debited.


    {/if}


 [3]: http://www.paypoint.net/solutions/payment-gateway/
 [6]: #installation
 [7]: #variables
 [8]: #response
 [14]: http://www.xe.com/iso4217.php
 [15]: http://cartthrob.com/docs