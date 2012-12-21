<?php

class jweather {

    private $debugCount = 1;
    private $debug = true;
    private $location;
    private $unit;
    private $imgfolder;

    private function debug($str, $title = 'Debug') {
        if ($this->debug) {
            echo '<br/><fieldset><legend>'.$title.' - '.$this->debugCount.'</legend><pre>';
            print_r($str);
            echo '<pre></fieldset><br/>';
            $this->debugCount++;
        }
    }

    private function show() {
        $query = "select * from weather.forecast where location = '".$this->location."' and u = '".$this->unit."'";
        $url = 'http://query.yahooapis.com/v1/public/yql?q='.urlencode($query).'&rnd='.date('Y') . (date('n')-1) . date('w') . date('G') .'&format=json';

        $this->debug($url);

        $jsonStr = file_get_contents($url);
        $phpobj = json_decode($jsonStr);

        $feed = $phpobj->query->results->channel;

		$wd = $feed->wind->direction;
		if ($wd>=348.75&&$wd<=360){$wd="N";}
        if ($wd>=0&&$wd<11.25){$wd="N";}
        if ($wd>=11.25&&$wd<33.75){$wd="NNE";}
        if ($wd>=33.75&&$wd<56.25){$wd="NE";}
        if ($wd>=56.25&&$wd<78.75){$wd="ENE";}
        if ($wd>=78.75&&$wd<101.25){$wd="L";}
        if ($wd>=101.25&&$wd<123.75){$wd="ESE";}
        if ($wd>=123.75&&$wd<146.25){$wd="SE";}
        if ($wd>=146.25&&$wd<168.75){$wd="SSE";}
        if ($wd>=168.75&&$wd<191.25){$wd="S";}
        if ($wd>=191.25&&$wd<213.75){$wd="SSO";}
        if ($wd>=213.75&&$wd<236.25){$wd="SO";}
        if ($wd>=236.25&&$wd<258.75){$wd="OSO";}
        if ($wd>=258.75&&$wd<281.25){$wd="O";}
        if ($wd>=281.25&&$wd<303.75){$wd="ONO";}
        if ($wd>=303.75&&$wd<326.25){$wd="NO";}
        if ($wd>=326.25&&$wd<348.75){$wd="NNO";}

		$wf = $feed->item->forecast[0];
		
		// Determine Day or Night
		$pubdate = $feed->item->pubDate;
        $n = strpos($pubdate, ":");
        $pubdate = substr($pubdate,$n-2,8);

        $tpb = strtotime($pubdate);
        $tsr = strtotime($feed->astronomy->sunrise);
        $tss = strtotime($feed->astronomy->sunset);

		if ($tpb>$tsr && $tpb<$tss) {
            $daynight = 'd';
        } else {
            $daynight = 'n';
        }

        $condition_text = array("Tornado", "Tempestade tropical", "Furacão", "Tempestades severas", "Trovoadas", "Chuva e neve misturadas", "Chuva misturada com granizo", "Neve misturada com granizo", "Garoa congelante", "Garoa", "Chuva Gelada", "Chovendo", "Chovendo", "Flocos de neve", "Chuva com neve", "Neve com vento", "Neve", "Granizo", "Geada", "Poeira", "Nebuloso", "Neblina", "Enfumaçado", "Rajadas de vento", "Ventania", "Frio", "Nublado", "Muito nublado (noite)", "Muito nublado (dia)", "Parcialmente nublado (noite)", "Parcialmente nublado (dia)", "Claro (noite)", "Ensolarado", "Muito claro (noite)", "Muito claro (dia)", "Chuva e granizo misturado", "Quente", "Trovoadas isoladas", "Parcialmente nublado", "Parcialmente nublado", "Chuvas esparsas", "Neve pesada", "Dispersos períodos de neve", "Neve pesada", "Parcialmente nublado", "Chuva com trovoadas", "Chuva com neve", "Trovoadas isoladas", 3200 => "Não disponível");

        $this->debug($condition_text);

		$html =  '<div class="jweatherMain">';
		$html .= '<div class="jweatherItem" style="background-image: url('.$this->imgfolder . $feed->item->condition->code . $daynight .'.png); background-repeat: no-repeat;">';
		$html .= '<div class="jweatherCity">'. $feed->location->city .'</div>';
		$html .= '<div class="jweatherTemp">'. $feed->item->condition->temp .'&deg;'. $feed->units->temperature .'</div>';
		$html .= '<div class="jweatherDesc">'. $condition_text[$feed->item->condition->code] .'</div>';
		$html .= '<div class="jweatherRange">Max: '. $wf->high .'&deg; Min: '. $wf->low .'&deg;</div>';
		$html .= '<div class="jweatherWind">Vento: '. $wd .' '. $feed->wind->speed .' '. $feed->units->speed .'</div>';
		$html .= '<div class="jweatherLink"><a target="_blank" href="'. $feed->item->link .'">Previs&atilde;o Completa</a></div>';
		$html .= '</div>';
		$html .= '</div>';


        echo $html;
    }

    public function __construct($location = 'BRXX0215',$unit = 'c', $imgfolder = 'img/', $debug = false) {
        try {
            if (is_string($location)) {
                $this->location = $location;
            } else {
                throw new Exception("Invalid argument - LOCATION should be a STRING, but it is an ".gettype($location).".");
            }

            if (is_string($unit)) {
                $this->unit = $unit;
            } else {
                throw new Exception("Invalid argument - UNIT should be a STRING, but it is an ".gettype($unit).".");
            }

            if (is_string($imgfolder)) {
                $this->imgfolder = $imgfolder;
            } else {
                throw new Exception("Invalid argument - IMG FOLDER should be a STRING, but it is an ".gettype($imgfolder).".");
            }

            if (is_bool($debug)) {
                $this->debug = $debug;
            } else {
                throw new Exception("Invalid argument - DEBUG should be a BOOLEAN, but it is an ".gettype($debug).".");
            }

            $this->debug("Location: $location\nUnit: $unit\nDebug: $debug");

            $this->show();

        } catch(Exception $e) {
            $this->debug($e->getMessage(),'Exception');
        }
    }
}
