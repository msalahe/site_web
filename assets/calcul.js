
function prixDeVenteHT(event){

    const prix = $('#composant_DUHT').val($('#composant_DUHT').val().replaceAll(',','.'));
    const marge = $('#composant_marge').val($('#composant_marge').val().replaceAll(',','.'));

    const prixDeVenteHT = prix * marge ;

    $('#composant_prix_de_vente').val(prixDeVente.toFixed(3));

}

