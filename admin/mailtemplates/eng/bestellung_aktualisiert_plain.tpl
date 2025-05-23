{includeMailTemplate template=header type=plain}

Dear {$Kunde->cVorname} {$Kunde->cNachname},

Your order at {$Einstellungen.global.global_shopname} has been updated.

Your order with the order number {$Bestellung->cBestellNr} consists of the following items:

{foreach $Bestellung->Positionen as $Position}

    {if $Position->nPosTyp == 1}
        {$Position->nAnzahl}x {$Position->cName} - {$Position->cGesamtpreisLocalized[$NettoPreise]}{if $Einstellungen.kaufabwicklung.bestellvorgang_lieferstatus_anzeigen === 'Y' && $Position->cLieferstatus}

        Delivery time: {$Position->cLieferstatus}{/if}
        {foreach $Position->WarenkorbPosEigenschaftArr as $WKPosEigenschaft}

            {$WKPosEigenschaft->cEigenschaftName}: {$WKPosEigenschaft->cEigenschaftWertName}{/foreach}
        {if !empty($Position->cSeriennummer)}
            Serialnumber: {$Position->cSeriennummer}
        {/if}
        {if !empty($Position->dMHD)}
            Shelf life expiration date: {$Position->dMHD}
        {/if}
        {if !empty($Position->cChargeNr)}
            Batch: {$Position->cChargeNr}
        {/if}
    {else}
        {$Position->nAnzahl}x {$Position->cName} - {$Position->cGesamtpreisLocalized[$NettoPreise]}{/if}
{/foreach}

{if $Einstellungen.global.global_steuerpos_anzeigen !== 'N'}{foreach $Bestellung->Steuerpositionen as $Steuerposition}
    {$Steuerposition->cName}: {$Steuerposition->cPreisLocalized}
{/foreach}{/if}
{if $Bestellung->GuthabenNutzen|default:0 === 1}
    Voucher: -{$Bestellung->GutscheinLocalized}
{/if}

Total: {$Bestellung->WarensummeLocalized[0]}


Your billing address:

{$Bestellung->oRechnungsadresse->cVorname} {$Bestellung->oRechnungsadresse->cNachname}
{$Bestellung->oRechnungsadresse->cStrasse} {$Bestellung->oRechnungsadresse->cHausnummer}
{if $Bestellung->oRechnungsadresse->cAdressZusatz}{$Bestellung->oRechnungsadresse->cAdressZusatz}{/if}
{$Bestellung->oRechnungsadresse->cPLZ} {$Bestellung->oRechnungsadresse->cOrt}
{if $Bestellung->oRechnungsadresse->cBundesland}{$Bestellung->oRechnungsadresse->cBundesland}{/if}
{if $Bestellung->oRechnungsadresse->cTel}Phone: {substr($Bestellung->oRechnungsadresse->cTel, 0, 2)}****{substr($Bestellung->oRechnungsadresse->cTel, -4)}
{/if}{if $Bestellung->oRechnungsadresse->cMobil}Mobile: {substr($Bestellung->oRechnungsadresse->cMobil, 0, 2)}****{substr($Bestellung->oRechnungsadresse->cMobil, -4)}
{/if}{if $Kunde->cFax}Fax: {$Kunde->cFax}
{/if}
Email: {$Bestellung->oRechnungsadresse->cMail}
{if $Kunde->cUSTID}VAT ID: {$Kunde->cUSTID}
{/if}

{if $Bestellung->Lieferadresse->kLieferadresse>0}
    Your delivery address:

    {$Bestellung->Lieferadresse->cVorname} {$Bestellung->Lieferadresse->cNachname}
    {$Bestellung->Lieferadresse->cStrasse} {$Bestellung->Lieferadresse->cHausnummer}
    {if $Bestellung->Lieferadresse->cAdressZusatz}{$Bestellung->Lieferadresse->cAdressZusatz}
    {/if}{$Bestellung->Lieferadresse->cPLZ} {$Bestellung->Lieferadresse->cOrt}
    {if $Bestellung->Lieferadresse->cBundesland}{$Bestellung->Lieferadresse->cBundesland}
    {/if}{$Bestellung->Lieferadresse->angezeigtesLand}
    {if $Bestellung->Lieferadresse->cTel}Tel: {substr($Bestellung->Lieferadresse->cTel, 0, 2)}****{substr($Bestellung->Lieferadresse->cTel, -4)}
    {/if}{if $Bestellung->Lieferadresse->cMobil}Mobile: {substr($Bestellung->Lieferadresse->cMobil, 0, 2)}****{substr($Bestellung->Lieferadresse->cMobil, -4)}
{/if}{if $Bestellung->Lieferadresse->cFax}Fax: {$Bestellung->Lieferadresse->cFax}
{/if}{if $Bestellung->Lieferadresse->cMail}Email: {$Bestellung->Lieferadresse->cMail}
{/if}
{else}
    Delivery address same as billing address.
{/if}

You have chosen the following payment option: {$Bestellung->cZahlungsartName}

{if !empty($Zahlungsart->cHinweisText)} {$Zahlungsart->cHinweisText}


{/if}

{if $Bestellung->Zahlungsart->cModulId === 'za_rechnung_jtl'}
{elseif $Bestellung->Zahlungsart->cModulId === 'za_lastschrift_jtl'}
{elseif $Bestellung->Zahlungsart->cModulId === 'za_barzahlung_jtl'}
{elseif $Bestellung->Zahlungsart->cModulId === 'za_paypal_jtl'}
{/if}

You will be notified of the status of your order separately.


Yours sincerely,
{$Firma->cName}

{includeMailTemplate template=footer type=plain}
