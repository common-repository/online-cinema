<?php
class WPOC_Parser {
    function wpoc_get_data( $link ) {
        $data = wp_remote_get( $link );
        return json_decode( $data['body'] );
    }
    function wpoc_link( $type, $tmdb_id, $option = '' ) {
        if ( 'images' !== $option ) {
            $lang = '&language=' . esc_attr( get_bloginfo( 'language' ) );
        } else {
            $lang = '';
        }
        if ( ! empty( $option ) ) {
            $option = '/' . $option;
        }
        return 'https://api.themoviedb.org/3/' . $type . '/' . esc_attr( $tmdb_id ) . esc_attr( $option ) . '?api_key=' . esc_attr( get_option( 'tmdb_api_key' ) ) . esc_attr( $lang );
    }
    function wpoc_credits_str( $credits, $tax ) {
        $array = array(
            'cast' => 'actors',
            'crew' => 'crew',
        );
        foreach ( $array as $key => $v ) {
            foreach ( $credits->$key as $person ) {
                if ( 'cast' === $key ) {
                    $s = $person->character;
                } else {
                    $s = $person->job . ';' . $person->department;
                }
                $value = array(
                    'name' => $person->name,
                    'adition' => $s,
                    'image' => $person->profile_path,
                );
                $str[ $v ] .= $tax->wpoc_input_members( $v, $value );
            }
        }
        return $str;
    }
    function wpoc_creators( $creators, $tax ) {
        foreach ( $creators as $creator ) {
            $value = array(
                'name' => $creator->name,
                'image' => $creator->profile_path,
            );
            $str .= $tax->wpoc_input_members( 'creators', $value );
        }
        return $str;
    }
    function wpoc_companies_list( $data, $tax ) {
        foreach ( $data as $company ) {
            $value = array(
                'name' => $company->name . '(' . $company->origin_country . ')',
                'image' => $company->logo_path,
            );
            $str .= $tax->wpoc_input_members( 'companies', $value );
        }
        return $str;
    }
    function wpoc_taxonomy( $det, $tax, $key ) {
        foreach ( $det->$key as $d ) {
            if ( 'production_countries' === $key ) {
                $key = 'countries';
            }
            $str .= $tax->wpoc_input( $key, $d->name );
        }
        return $str;
    }
    function wpoc_trailer( $videos ) {
        foreach ( $videos as $video ) {
            if( 'YouTube' === $video->site && 'Trailer' === $video->type ) {
                return $video->key;
            }
        }
    }
    function wpoc_images( $data, $types, $key ) {
        $results = $data->$key;
        return $types->wpoc_table_section( $key, $results[0]->file_path );
    }
    function wpoc_tvimages( $data, $types, $key ) {
        $k = $key . '_path';
        $results = $data->$k;
        return $types->wpoc_table_section( $key . 's', $results );
    }
}