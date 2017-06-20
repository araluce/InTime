<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Utils;

/**
 * Algunos métodos para descargar información de twitter, formatearla y mostrarla
 *
 * @author araluce
 */
class Twitter {

    /**
     * Descarga los tweets de un usuario de twitter y los retorna en formato
     * para consumir en la vista
     * @param type $doctrine
     * @param <AppBundle/Entity/Usuario> $USUARIO
     * @param string $usuario_tw
     * @param int $count número de tweets a descargar
     * @return array
     */
    static function twitter($doctrine, $USUARIO, $usuario_tw, $count) {
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
     * @param array $tweets
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
            if (isset($tweet['extended_entities'])) {
                if (isset($tweet['extended_entities']['media'])) {
                    foreach ($tweet['extended_entities']['media'] as $media) {
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
     * @param int $usuario_tw Usuario de twitter del que descargar sus tweets
     * @param int $count offset del número de tweets a descargar
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
    
    /**
     * Descarga tweets con el hangstag #ProyectoSinTime
     * @return array
     */
    static function tweetsProyectoSinTime() {
        $settings = array(
            'oauth_access_token' => "743191767164592129-ZQwH3OpEmZ7lji1Lo7xGNd9cJGzbI5x",
            'oauth_access_token_secret' => "Im8zcvNeTUQvCLuCp7PR9dPNE2GZCiISt7qLXReZ3Fd8Y",
            'consumer_key' => "wNhHfRrCJiKqtpGKNoESzZf10",
            'consumer_secret' => "UFrFsCIIMwRZK5KVGAfUBAb5VY53EuXmdTReirOGyi6uFJZ0vn"
        );
        $url = "https://api.twitter.com/1.1/search/tweets.json";
        $requestMethod = "GET";
        $getfield = "?q=%23proyectosintime";
        $twitter = new \AppBundle\Twitter\TwitterAPIExchange($settings);
        $string = json_decode($twitter->setGetfield($getfield)
                        ->buildOauth($url, $requestMethod)
                        ->performRequest(), $assoc = TRUE);
//        return $string;
        return Twitter::tweetsFormato2($string);
    }
    
    /**
     * Devuelve los tweets en un formato que pueda ser leido por la vista más fácilmente
     * @param array $tweets
     * @return array|null
     */
    static function tweetsFormato2($tweets) {
        if (isset($tweets['errors']) ||
                isset($tweets['error'])) {
            return null;
        }
        $DATOS = [];
        $DATOS['TWEETS'] = [];
        foreach ($tweets['statuses'] as $tweet) {
            $info = [];
            $info['FECHA'] = $tweet['created_at'];
            $info['TWEET'] = str_replace("\\", "", $tweet['text']);
            $info['RETWEET'] = false;
            $info['VIDEO'] = false;
            $info['FOTO'] = false;
            $info['VIDEOS'] = [];
            $info['FOTOS'] = [];
            if (isset($tweet['retweeted_status'])) {
//                $info['RETWEET'] = true;
//                $info['ID_STR'] = $tweet['retweeted_status']['id_str'];
//                $info['IMG_PERFIL'] = $tweet['retweeted_status']['user']['profile_image_url'];
//                $info['NOMBRE_COMP'] = $tweet['retweeted_status']['user']['name'];
//                $info['ALIAS'] = $tweet['retweeted_status']['user']['screen_name'];
                $info['ID_STR'] = $tweet['id_str'];
                $info['IMG_PERFIL'] = $tweet['user']['profile_image_url'];
                $info['NOMBRE_COMP'] = $tweet['user']['name'];
                $info['ALIAS'] = $tweet['user']['screen_name'];
            } else {
                $info['ID_STR'] = $tweet['id_str'];
                $info['IMG_PERFIL'] = $tweet['user']['profile_image_url'];
                $info['NOMBRE_COMP'] = $tweet['user']['name'];
                $info['ALIAS'] = $tweet['user']['screen_name'];
            }
            if (isset($tweet['extended_entities'])) {
                if (isset($tweet['extended_entities']['media'])) {
                    foreach ($tweet['extended_entities']['media'] as $media) {
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
            $DATOS['TWEETS'][] = $info;
        }
        return $DATOS;
    }

    /**
     * Descarga un tweet específico
     * @param type $doctrine
     * @param <AppBundle/Entity/Tweet> $tweet
     * @return json
     */
    static function descargarTweet($doctrine, $tweet) {
        $settings = array(
            'oauth_access_token' => "743191767164592129-ZQwH3OpEmZ7lji1Lo7xGNd9cJGzbI5x",
            'oauth_access_token_secret' => "Im8zcvNeTUQvCLuCp7PR9dPNE2GZCiISt7qLXReZ3Fd8Y",
            'consumer_key' => "wNhHfRrCJiKqtpGKNoESzZf10",
            'consumer_secret' => "UFrFsCIIMwRZK5KVGAfUBAb5VY53EuXmdTReirOGyi6uFJZ0vn"
        );
        $url = 'https://api.twitter.com/1.1/statuses/show.json';
        $requestMethod = "GET";
        $getfield = "?id=" . $tweet->getIdTweet();
        $twitter = new \AppBundle\Twitter\TwitterAPIExchange($settings);
        $tweet_json = json_decode($twitter->setGetfield($getfield)
                        ->buildOauth($url, $requestMethod)
                        ->performRequest(), $assoc = TRUE);
        return $tweet_json;
    }

    /**
     * Almacena un tweet en una mochila específica de un ciudadano específico
     * @param int $id_tuitero
     * @param int $id_tweet
     * @param int $tipo_tweet
     * @param int $id_usu
     * @param int $id_usu_des
     * @param <\DateTime> $fecha
     * @param type $doctrine
     * @return int
     */
    static function almacenar_tweet($id_tuitero, $id_tweet, $tipo_tweet, $id_usu, $id_usu_des, $fecha, $doctrine) {
        $em = $doctrine->getManager();

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
            
            $mochila_anterior = $doctrine->getRepository('AppBundle:MochilaTweets')->findOneBy([
                'idUsuario' => $id_usu, 'idUsuarioDestino' => $id_usu_des, 'idTweet' => $id_tweet
            ]);
            if(null !== $mochila_anterior){
                $em->remove($mochila_anterior);
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

        return 1;
    }

    /**
     * Obtiene la fecha de creación de un tweet específico
     * @param int $id_tweet
     * @return int|string
     */
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
    
    /**
     * Obtiene todos los tweets alojados en la mochila de un ciudadano específico
     * @param type $doctrine
     * @param type $USUARIO
     * @param type $TIPO_TWEET
     * @return int
     */
    static function getMochila($doctrine, $USUARIO, $TIPO_TWEET) {
        $MOCHILAS = Twitter::getIdsTweetsMochilaTipo($doctrine, $USUARIO, $TIPO_TWEET);
        if(!count($MOCHILAS)){
            return 0;
        }
        $DATOS = [];
        $TWEETS = [];
        foreach($MOCHILAS as $MOCHILA){
            $TWEET = $doctrine->getRepository('AppBundle:Tweet')->findOneByIdTweet($MOCHILA->getIdTweet());
            $TWEETS[] = Twitter::descargarTweet($doctrine, $TWEET);
        }
        $DATOS['TWEETS'] = Twitter::tweetsFormato($TWEETS);
        return $DATOS;
    }

    /**
     * Obtiene todos los ids de los tweets almacenados en una mochila específica de un usuario
     * @param type $doctrine
     * @param type $USUARIO
     * @param type $TIPO_TWEET
     * @return array
     */
    static function getIdsTweetsMochilaTipo($doctrine, $USUARIO, $TIPO_TWEET) {
        $id_tweets = [];
        $id_tweets = $doctrine->getRepository('AppBundle:MochilaTweets')->findBy([
            'idUsuarioDestino' => $USUARIO, 'idTipoTweet' => $TIPO_TWEET
        ]);
        return $id_tweets;
    }

}
