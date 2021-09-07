jQuery(function($){


const apitabsTabs = $('#apitabs-tabs .cat-item');
const apitabsPostsContainer = $('#apitabs-posts-container');

$(document).ready(function(){
  if (apitabsTabs) {
    // po kliknięciu na tab z kategorią
    apitabsTabs.on('click', function(){
      apitabsPostsContainer.html('');
      var itemClass = $(this).attr("class");
      var itemNumber = itemClass.replace('cat-item cat-item-','');
      // console.log(itemNumber);
      const ourRequest = new XMLHttpRequest();
      let endpoint = document.location.origin + '/wp-json/wl/v1/posts/categories=' + itemNumber;
      console.log(endpoint);
      ourRequest.open('GET', endpoint);
      ourRequest.onload = function() {
        if (ourRequest.status >= 200 && ourRequest.status < 400) {
          const data = JSON.parse(ourRequest.responseText);
          createHTML(data);
        } else {
          console.log("Połączono z serwerem, ale wyrzuciło błąd");
        }
      };

      ourRequest.onerror = function(){
        console.log('Błąd połączenia');
      };

      ourRequest.send();
    })
  }
});

// wyrzucenie listy wpisów
function createHTML(postsData){
  let ourHTMLString = '';
  console.log(postsData);
  for (let i = 0; i < postsData.length; i++) {
    ourHTMLString += '<h2>' + postsData[i].title + '</h2>';
    ourHTMLString += postsData[i].content;
  }
  apitabsPostsContainer.html(ourHTMLString);
}

});