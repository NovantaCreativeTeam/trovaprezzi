Nome |Marca |Descrizione |Prezzo Vendita |Codice Interno |Link all’offerta|Disponibilità |Albero Categorie |Link Immagine |Spese di Spedizione |Codice Produttore |Codice EAN |Peso |Ulteriore Link Immagine 1 | Ulteriore Link Immagine 2 <endrecord>
{foreach from=$products item=$product}
    {$product.name}|{$product.manufacturer}|{$product.description}|{$product.price}|{$product.reference}|{$product.link_rewrite}|{$product.avaibility}|{{$product.category_tree}|{$product.image_url}|{$product.shipping_cost}|{$product.manufacturer_reference}|{$product.ean13}|{$product.weight}{$endrecord}
{/foreach}}