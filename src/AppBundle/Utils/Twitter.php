<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Utils;

/**
 * Description of Twitter
 *
 * @author araluce
 */
class Twitter {

    static function twitter($USUARIO, $usuario_twitter, $count, $doctrine) {
        /** Set access tokens here - see: https://dev.twitter.com/apps/ * */
        $settings = array(
            'oauth_access_token' => "743191767164592129-ZQwH3OpEmZ7lji1Lo7xGNd9cJGzbI5x",
            'oauth_access_token_secret' => "Im8zcvNeTUQvCLuCp7PR9dPNE2GZCiISt7qLXReZ3Fd8Y",
            'consumer_key' => "wNhHfRrCJiKqtpGKNoESzZf10",
            'consumer_secret' => "UFrFsCIIMwRZK5KVGAfUBAb5VY53EuXmdTReirOGyi6uFJZ0vn"
        );

        $user = strtolower($usuario_twitter);

        $seguidos = $doctrine->getRepository('AppBundle:UsuarioXTuitero')->findByIdUsuario($USUARIO);

        $url = "https://api.twitter.com/1.1/statuses/user_timeline.json";
        $requestMethod = "GET";
        $getfield = "?screen_name=$user&count=$count";
        $twitter = new \AppBundle\Twitter\TwitterAPIExchange($settings);
        $string = json_decode($twitter->setGetfield($getfield)
                        ->buildOauth($url, $requestMethod)
                        ->performRequest(), $assoc = TRUE);

        $twiteer = $doctrine->getRepository('AppBundle:UsuarioXTuitero')->findOneBy(['idUsuario' => $USUARIO, 'idTuitero' => $user]);
        if (!isset($string['errors']) && !isset($string['error']) && null === $twiteer) {
            $em = $doctrine->getManager();
            $twiteer = new \AppBundle\Entity\UsuarioXTuitero();
            $twiteer->setIdUsuario($USUARIO);
            $twiteer->setIdTuitero($user);
            $em->persist($twiteer);
            $em->flush();
        }
        return $string;
    }

    static function twitter2($doctrine, $USUARIO, $usuario_tw, $count) {
        $tweetsSinFormato = Twitter::descargarTweets(strtolower($usuario_tw), $count);
        $twiteer = $doctrine->getRepository('AppBundle:UsuarioXTuitero')->findOneBy(
                ['idUsuario' => $USUARIO, 'idTuitero' => strtolower($usuario_tw)]
        );
        if (!isset($tweetsSinFormato['errors']) && !isset($tweetsSinFormato['error']) && null === $twiteer) {
            $em = $doctrine->getManager();
            $twiteer = new \AppBundle\Entity\UsuarioXTuitero();
            $twiteer->setIdUsuario($USUARIO);
            $twiteer->setIdTuitero(strtolower($usuario_tw));
            $em->persist($twiteer);
            $em->flush();
        }
        $tweets = [];
        $tweets['ALIAS'] = strtolower($usuario_tw);
        $tweets['TWEETS'] = Twitter::tweetsFormato($tweetsSinFormato);

        return $tweets;
    }

    /**
     * Devuelve los tweets en un formato que pueda ser leido por la vista más fácilmente
     * @param type $tweets
     * @return array|null
     */
    static function tweetsFormato($tweets) {
        if (isset($tweets['errors']) ||
                isset($tweets['error'])) {
            return null;
        }
        $DATOS = [];
        foreach ($tweets as $tweet) {
            $info = [];
            $info['FECHA'] = $tweet['created_at'];
            $info['TWEET'] = str_replace("\\", "", $tweet['text']);
            $info['RETWEET'] = false;
            $info['VIDEO'] = false;
            $info['FOTO'] = false;
            $info['VIDEOS'] = [];
            $info['FOTOS'] = [];
            if (isset($tweet['retweeted_status'])) {
                $info['RETWEET'] = true;
                $info['ID_STR'] = $tweet['retweeted_status']['id_str'];
                $info['IMG_PERFIL'] = $tweet['retweeted_status']['user']['profile_image_url'];
                $info['NOMBRE_COMP'] = $tweet['retweeted_status']['user']['name'];
                $info['ALIAS'] = $tweet['retweeted_status']['user']['screen_name'];
            } else {
                $info['ID_STR'] = $tweet['id_str'];
                $info['IMG_PERFIL'] = $tweet['user']['profile_image_url'];
                $info['NOMBRE_COMP'] = $tweet['user']['name'];
                $info['ALIAS'] = $tweet['user']['screen_name'];
            }
            if(isset($tweet['extended_entities'])){
                if(isset($tweet['extended_entities']['media'])){
                    foreach($tweet['extended_entities']['media'] as $media){
                        if (isset($media['video_info'])) {
                            $info['VIDEO'] = true;
                            $video = str_replace("\\", "", $media['video_info']['variants'][0]['url']);
                            $info['VIDEOS'][] = $video;
                        }
                        if (isset($media['media_url'])) {
                            $info['FOTO'] = true;
                            $foto = str_replace("\\", "", $media['media_url']);
                            $info['FOTOS'][] = $foto;
                        }
                    }
                }
            }
            $DATOS[] = $info;
        }
        return $DATOS;
    }

    /**
     * Descarga un número específico de tweets de un usuario específico de twitter
     * @param type $usuario_tw
     * @param type $count
     * @return array
     */
    static function descargarTweets($usuario_tw, $count) {
        $settings = array(
            'oauth_access_token' => "743191767164592129-ZQwH3OpEmZ7lji1Lo7xGNd9cJGzbI5x",
            'oauth_access_token_secret' => "Im8zcvNeTUQvCLuCp7PR9dPNE2GZCiISt7qLXReZ3Fd8Y",
            'consumer_key' => "wNhHfRrCJiKqtpGKNoESzZf10",
            'consumer_secret' => "UFrFsCIIMwRZK5KVGAfUBAb5VY53EuXmdTReirOGyi6uFJZ0vn"
        );

        $url = "https://api.twitter.com/1.1/statuses/user_timeline.json";
        $requestMethod = "GET";
        $getfield = "?screen_name=$usuario_tw&count=$count";
        $twitter = new \AppBundle\Twitter\TwitterAPIExchange($settings);
        $string = json_decode($twitter->setGetfield($getfield)
                        ->buildOauth($url, $requestMethod)
                        ->performRequest(), $assoc = TRUE);
        return $string;
    }

    static function almacenar_tweet($id_tuitero, $id_tweet, $tipo_tweet, $id_usu, $id_usu_des, $fecha, $doctrine) {
        $em = $doctrine->getManager();

// Si no existe usuario destino, pasa a ser igual que el usuario origen
        if ($id_usu_des === null) {
            $id_usu_des = $id_usu;
        }

// Si $id_tweet === null => estamos solicitando qué tenemos en la mochila en este momento
        if ($id_tweet !== 'null') {
// Buscamos si el tweet ya está almacenado
            $tweet = $doctrine->getRepository('AppBundle:Tweet')->findOneByIdTweet($id_tweet);
            $tipo_tweet = $doctrine->getRepository('AppBundle:TipoTweet')->findOneById($tipo_tweet);
            $id_usu = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usu);
            $id_usu_des = $doctrine->getRepository('AppBundle:Usuario')->findOneByIdUsuario($id_usu_des);

// Si no está almacenado, lo almacenamos
            if ($tweet === null) {
                $tweet = new \AppBundle\Entity\Tweet();
                $tweet->setIdTweet(intval($id_tweet));
                $tweet->setIdTuitero($id_tuitero);
                $tweet->setFecha($fecha);
                $em->persist($tweet);
                $em->flush();
            }

            $mochila = $doctrine->getRepository('AppBundle:MochilaTweets')->findByIdTweet($id_tweet);
            $existe = false;
            if ($mochila) {
                foreach ($mochila as $t) {
                    if ($t->getIdUsuario() === $id_usu &&
                            $t->getIdUsuarioDestino() === $id_usu_des &&
                            $t->getIdTipoTweet() === $tipo_tweet) {
                        $existe = true;
                        break;
                    }
                }
            }

// Si el tweet no está almacenado en la mochila lo almacenamos
            if (!$existe) {
                $compartir = new \AppBundle\Entity\MochilaTweets();
                $compartir->setIdTweet($id_tweet);
                $compartir->setIdUsuario($id_usu);
                $compartir->setIdUsuarioDestino($id_usu_des);
                $compartir->setIdTipoTweet($tipo_tweet);
                $compartir->setFecha(new \DateTime('now'));
                $em->persist($compartir);
                $em->flush();
            }
        }

        $respuesta = \AppBundle\Utils\Twitter::actualizar_mochilas($id_usu, $doctrine);

        return $respuesta;
    }

    static function actualizar_mochilas($id_usu, $doctrine) {
        $texto = $doctrine->getRepository('AppBundle:MochilaTweets')->findBy(['idUsuario' => $id_usu, 'idTipoTweet' => 1]);
        $multimedia = $doctrine->getRepository('AppBundle:MochilaTweets')->findBy(['idUsuario' => $id_usu, 'idTipoTweet' => 2]);
        $recursos = $doctrine->getRepository('AppBundle:MochilaTweets')->findBy(['idUsuario' => $id_usu, 'idTipoTweet' => 3]);
        $compartidos = $doctrine->getRepository('AppBundle:MochilaTweets')->findBy(['idUsuarioDestino' => $id_usu, 'idTipoTweet' => 4]);

        $respuesta = [];
        $respuesta['texto'] = [];
        $respuesta['multimedia'] = [];
        $respuesta['recursos'] = [];
        $respuesta['compartidos'] = [];

        if ($texto) {
            foreach ($texto as $elemento) {
                $respuesta['texto'][] = $elemento->getIdMochila();
            }
        }
        if ($multimedia) {
            foreach ($multimedia as $elemento) {
                $respuesta['multimedia'][] = $elemento->getIdMochila();
            }
        }
        if ($recursos) {
            foreach ($recursos as $elemento) {
                $respuesta['recursos'][] = $elemento->getIdMochila();
            }
        }
        if ($compartidos) {
            foreach ($compartidos as $elemento) {
                $respuesta['compartidos'][] = $elemento->getIdMochila();
            }
        }
        return $respuesta;
    }

    static function getFecha($id_tweet) {
// Preparamos los datos para conectar y la consulta a realizar
        $settings = array(
            'oauth_access_token' => "743191767164592129-ZQwH3OpEmZ7lji1Lo7xGNd9cJGzbI5x",
            'oauth_access_token_secret' => "Im8zcvNeTUQvCLuCp7PR9dPNE2GZCiISt7qLXReZ3Fd8Y",
            'consumer_key' => "wNhHfRrCJiKqtpGKNoESzZf10",
            'consumer_secret' => "UFrFsCIIMwRZK5KVGAfUBAb5VY53EuXmdTReirOGyi6uFJZ0vn"
        );

        $url = 'https://api.twitter.com/1.1/statuses/show.json';
        $requestMethod = "GET";
        $getfield = "?id=$id_tweet";
        $twitter = new \AppBundle\Twitter\TwitterAPIExchange($settings);
        $tweet = json_decode($twitter->setGetfield($getfield)
                        ->buildOauth($url, $requestMethod)
                        ->performRequest(), $assoc = TRUE);

        if (isset($tweet['created_at'])) {
            return $tweet['created_at'];
        }
        return 0;
    }

    static function getTweetByIdMochila($id_mochila, $doctrine) {
        $id_mochila = $doctrine->getRepository('AppBundle:MochilaTweets')->findOneByIdMochila($id_mochila);
        $tweet = $doctrine->getRepository('AppBundle:Tweet')->findOneByIdTweet($id_mochila->getIdTweet());

// Preparamos los datos para conectar y la consulta a realizar
        $settings = array(
            'oauth_access_token' => "743191767164592129-ZQwH3OpEmZ7lji1Lo7xGNd9cJGzbI5x",
            'oauth_access_token_secret' => "Im8zcvNeTUQvCLuCp7PR9dPNE2GZCiISt7qLXReZ3Fd8Y",
            'consumer_key' => "wNhHfRrCJiKqtpGKNoESzZf10",
            'consumer_secret' => "UFrFsCIIMwRZK5KVGAfUBAb5VY53EuXmdTReirOGyi6uFJZ0vn"
        );
//        $url = "https://api.twitter.com/1.1/search/tweets.json";
//        $requestMethod = "GET";
//        $getfield = "?q=@" . $tweet->getIdTuitero() . "&max_id=" . $tweet->getIdTweet();
//        $twitter = new \AppBundle\Twitter\TwitterAPIExchange($settings);
//        $string = json_decode($twitter->setGetfield($getfield)
//                        ->buildOauth($url, $requestMethod)
//                        ->performRequest(), $assoc = TRUE);
        $url = 'https://api.twitter.com/1.1/statuses/show.json';
        $requestMethod = "GET";
        $getfield = "?id=" . $tweet->getIdTweet();
        $twitter = new \AppBundle\Twitter\TwitterAPIExchange($settings);
        $tweet_json = json_decode($twitter->setGetfield($getfield)
                        ->buildOauth($url, $requestMethod)
                        ->performRequest(), $assoc = TRUE);

        $info = [];
//\AppBundle\Utils\Utils::pretty_print($string);
        if (!isset($tweet_json['error'])) {
            $info['id_mochila'] = $id_mochila->getIdMochila();
            $info['alias'] = $id_mochila->getIdUsuario()->getSeudonimo();
            $info['created_at'] = $tweet_json['created_at'];
            $info['text'] = str_replace("\\", "", $tweet_json['text']);
            $info['user'] = [];
            $info['user']['name'] = $tweet_json['user']['name'];
            $info['user']['screen_name'] = $tweet_json['user']['screen_name'];
            $info['user']['profile_image_url'] = $tweet_json['user']['profile_image_url'];

            if (isset($tweet_json['extended_entities'])) {
                $info['extended_entities'] = [];
                $info['extended_entities']['media'] = [];
                $info_media = [];
                foreach ($tweet_json['extended_entities']['media'] as $media) {
                    $info_media['type'] = $media['type'];
                    if (isset($media['video_info'])) {
                        $info_media['video_info'] = [];
                        $info_media['video_info']['url'] = str_replace("\\", "", $media['video_info']['variants'][1]['url']);
                        $info['extended_entities']['media'][] = $info_media;
                    }
                    if (isset($media['media_url'])) {
                        $info_media['media_url'] = str_replace("\\", "", $media['media_url']);
                        $info['extended_entities']['media'][] = $info_media;
                    }
                }
            } else {
                $info['extended_entities'] = 'undefined';
            }
        }
//        if (!isset($string['error'])) {
//            $item = $string['statuses'][0];
//            $info['id_mochila'] = $id_mochila->getIdMochila();
//            $info['alias'] = $id_mochila->getIdUsuario()->getSeudonimo();
//            $info['created_at'] = $item['created_at'];
//            $info['text'] = str_replace("\\", "", $item['text']);
//            $info['retweeted_status'] = [];
//            $info['user'] = [];
//            if (isset($item['retweeted_status'])) {
//                $info['retweeted_status']['user'] = [];
//                $info['retweeted_status']['user']['name'] = $item['retweeted_status']['user']['name'];
//                $info['retweeted_status']['user']['screen_name'] = $item['retweeted_status']['user']['screen_name'];
//                $info['retweeted_status']['user']['profile_image_url'] = str_replace("\\", "", $item['retweeted_status']['user']['profile_image_url']);
//            }
//            $info['user']['name'] = $item['user']['name'];
//            $info['user']['screen_name'] = $item['user']['screen_name'];
//            $info['user']['profile_image_url'] = str_replace("\\", "", $item['user']['profile_image_url']);
//
//            $info['extended_entities'] = [];
//            if (isset($item['extended_entities'])) {
//                $info['extended_entities']['media'] = [];
//                $info_media = [];
//                foreach ($item['extended_entities']['media'] as $media) {
//                    $info_media['type'] = $media['type'];
//                    if (isset($media['video_info'])) {
//                        $info_media['video_info'] = [];
//                        $info_media['video_info']['url'] = str_replace("\\", "", $media['video_info']['variants'][1]['url']);
//                        $info['extended_entities']['media'][] = $info_media;
//                    }
//                    if (isset($media['media_url'])) {
//                        $info_media['media_url'] = str_replace("\\", "", $media['media_url']);
//                        $info['extended_entities']['media'][] = $info_media;
//                    }
//                }
//            }
//        }

        return $info;
    }

}
