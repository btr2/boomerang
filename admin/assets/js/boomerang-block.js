( function() {
    const { __ } = wp.i18n;
    const { registerBlockType } = wp.blocks;
    const el = wp.element.createElement;
    const { withSelect } = wp.data;
    const { SelectControl, ServerSideRender, Placeholder } = wp.components;
    var dispatch = wp.data.dispatch;
    dispatch( 'core' ).addEntities( [
        {
            name: 'boomerang_board',           // route name
            kind: 'wp/v2', // namespace
            baseURL: '/wp/v2/boomerang_board' // API path without /wp-json
        }
    ]);
    // Plugin logo    
    const iconEl = el('img', 
        { 
            src: boomerangGlobal.logoUrl,
            width: 150, 
            height: 150, 
            className: 'boomerang-logo',
        },
     );

     const icon_small = el('img', 
        { 
            src: boomerangGlobal.iconUrl,
            width: 50, 
            height: 50,
        },
     );


    registerBlockType( 'boomerang-block/shortcode-gutenberg', {
        title: __( 'Boomerang', 'boomerang' ),
        icon: icon_small,
        category: 'common',
        attributes: {
            board: {
                    type: 'string' 
                },        
        },
        edit: withSelect( function( select, props ) {
            return {
                posts: select( 'core' ).getEntityRecords( 'wp/v2', 'boomerang_board', { per_page: -1 } ),
                metaValue: select( 'core/editor' ),
            }
        } ) (function(props) {
            
            function onChangeBoomerangBoard( id ) {
                props.setAttributes( { board: id } );
            }

            var options = [];
            if( props.posts ) {
                options.push( { value: 0, label: __( 'Select a Boomerang Board', 'boomerang' ) } );
                props.posts.forEach((boomerang_board) => {
                    options.push({value:boomerang_board.id, label:boomerang_board.title.rendered});
                });
            } else {
                options.push( { value: 0, label: 'Loading...' } )
            }

            return [
                    el(
                        'div',
                        {
                          className:'placeholder-boomerang-block',
                        },
                        el(
                            Placeholder,
                            {
                              label: __( '', 'boomerang' ),
                              icon:iconEl,
                            },
                            el(
                                SelectControl,
                                 {
                                    label: __( '', 'boomerang' ),
                                    value: props.attributes.board,
                                    onChange: onChangeBoomerangBoard,
                                    options:options,
                                }
                            )
                        ),
                    ),
            ];

        }),
        save: function(props) {
           return [
               el( ServerSideRender, {
                    block: 'boomerang-block/shortcode-gutenberg',
                    attributes: props.attributes,
                } ) 
           ];
        },
    } );
}() );