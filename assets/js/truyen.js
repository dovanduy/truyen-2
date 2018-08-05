jQuery(document).ready(function($) {
                var engine = new Bloodhound({
                    remote: {
                        url: _url+'/findTitle?q=%QUERY%',
                        wildcard: '%QUERY%'
                    },
                    datumTokenizer: Bloodhound.tokenizers.whitespace('q'),
                    queryTokenizer: Bloodhound.tokenizers.whitespace
                });

                $(".search-input").typeahead({
                    hint: true,
                    highlight: true,
                    minLength: 2
                }, {
                    source: engine.ttAdapter(),
                    name: 'mobileList',
                    display: function(item){ 
                            return item.title
                        },
                    templates: {
                        empty: [
                            '<div class="list-group search-results-dropdown"><div class="list-group-item">Không có kết quả phù hợp.</div></div>'
                        ],
                        header: [
                            '<div class="list-group search-results-dropdown">'
                        ],
                        suggestion: function (data) {
                          console.log(data)
                          var html='<div class="list-group-item"><a href="'+_url+'/detail/'+data.id+'/'+data.slug+'">' + data.title + '</a></div>';
                            return html;
                        }
                    }
                });
            });