<?php
/*******************************************************************
* PHP Diff and Patch
* Copyright (C)2005 CS Wagner. <cs@kainaw.com>
* This is free software that you may use for any purpose that you
* see fit.  Just don't claim that you wrote it.
********************************************************************
* This file contains the diff and patch functions written for PHP.
* Unlike unix diff, these do not use files.  They use strings and
* arrays of strings.
* If you want Unix-like functionality (comparing files instead of
* strings), you'll have to write a function to open each file, read
* the data into a string, and then call diff.
********************************************************************
* diff( initial_string, changed_string, [minimum_match] )
*   initial_string: The initial string to be changed.
*   changed_string: The string containing changes.
*   minimum_match: (optional) Minimum number of characters to match.
* This will return an array of strings containing differences
* between the initial_string and the changed_string.  Each element
* of the diff array begins with an index and a - or +, meaning:
*   - This section has been removed.
*   + This section has been added.
* The optional minimum_match parameter will keep diff from matching
* up short sequences of letters.  Examples of minimum_match:
* diff("Sam", "Bart", 1) = ("0-S", "0+B","2-m", "2+rt")
* diff("Sam", "Bart", 2) = ("0-Sam", "0+Bart")
********************************************************************
* patch( initial_string, diff_array )
*   initial_string: The string to be patched.
*   diff_array: Array of differences.
* This will take a string and apply differences to it to create a
* new string containing all of the differences.
* Note: diff_array may be a single difference string, or an array
* of difference arrays.  Examples are:
* patch("Bart", "0-B") = "art"
* patch("Bart", array("0-B", "0+C")) = "Cart"
* patch("Bart", array(
*   array("0-B", "0+C"),
*   array("1-a", "1+ove"))) = "Covert"
* Using an array of diff arrays will allow you to store incremental
* changes and then apply multiple changes at once.
********************************************************************
* unpatch( final_string, diff_array )
*   This is functionally identical to patch() except that the diffs
*   are removed from the string.  This allows you to undo a patch
*   and get back the original string.
********************************************************************/

/**
* Calculate the differences between two strings.
* $a: Initial string
* $b: Changed string
* $min: (optional) minum match length
* return: array of changes
*/
function diff($a, $b, $min=3, $i=0)
{
    $diff = array();
    if($a == "" && $b == "") return $diff;
    if($a == "")
    {
        array_push($diff, "$i+".$b);
        return $diff;
    }
    if($b == "")
    {
        array_push($diff, "$i-".$a);
        return $diff;
    }
    $match = diff_match($a, $b);
    if(strlen($match) < $min)
    {
        array_push($diff, "$i-".$a);
        array_push($diff, "$i+".$b);
        return $diff;
    }
    $ap = strpos($a, $match);
    $bp = strpos($b, $match);
    $diff = diff(substr($a, 0, $ap), substr($b, 0, $bp), $min, $i);
    return array_merge($diff, diff(substr($a, $ap+strlen($match)), substr($b, $bp+strlen($match)), $min, $i+$bp+strlen($match)));
}

/**
* Find the longest match between two strings.
* The time limit must be turned off for this function.
* With short strings - you won't notice.
* With long strings, you will easily timeout in 30 seconds (PHP default).
* If you know you won't timeout and do not like turning it off, just remove
* the "set_time_limit(0)" line.
*/
function diff_match($a, $b, $level="line")
{
//    set_time_limit(0);
    $answer = "";
    if($level == "line" || $level == "word")
    {
        if($level == "line")
        {
            $as = explode("\n", $a);
            $bs = explode("\n", $b);
        }
        else
        {
            $as = explode(" ", $a);
            $bs = explode(" ", $b);
        }

        $last = array();
        $next = array();
        $start = -1;
        $len = 0;
        $answer = "";
        for($i = 0; $i < sizeof($as); $i++)
        {
            $start+= strlen($as[$i])+1;
            for($j = 0; $j < sizeof($bs); $j++)
            {
                if($as[$i] != $bs[$j])
                {
                    if(isset($next[$j])) unset($next[$j]);
                }
                else
                {
                    if(!isset($last[$j-1]))
                        $next[$j] = strlen($bs[$j]) + 1;
                    else
                        $next[$j] = strlen($bs[$j]) + $last[$j-1] + 1;
                    if($next[$j] > $len)
                    {
                        $len = $next[$j];
                        $answer = substr($a, $start-$len+1, $len);
                    }
                }
            }
            // If PHP ever copies pointers here instead of copying data,
            // this will fail.  They better add array_copy() if that happens.
            $last = $next;
        }
    }
    else
    {
        $m = strlen($a);
        $n = strlen($b);
        $last = array();
        $next = array();
        $len = 0;
        $answer = "";
        for($i = 0; $i < $m; $i++)
        {
            for($j = 0; $j < $n; $j++)
            {
                if($a[$i] != $b[$j])
                {
                    if(isset($next[$j])) unset($next[$j]);
                }
                else
                {
                    if(!isset($last[$j-1]))
                        $next[$j] = 1;
                    else
                        $next[$j] = 1 + $last[$j-1];
                    if($next[$j] > $len)
                    {
                        $len = $next[$j];
                        $answer = substr($a, $i-$len+1, $len);
                    }
                }
            }
            // If PHP ever copies pointers here instead of copying data,
            // this will fail.  They better add array_copy() if that happens.
            $last = $next;
        }
    }
    if($level == "line" && $answer == "") return diff_match($a, $b, "word");
    elseif($level == "word" && $answer == "") return diff_match($a, $b, "letter");
    else return $answer;
}

/**
* Patch a string with changes.
* $text: Initial string
* $diff: Change or array of Changes
* return: Patched string
*/
function patch($text, $diff)
{
    if(!is_array($diff))
    {
        $n = 0;
        for($i=0; $i<strlen($diff); $i++)
        {
            $c = substr($diff, $i, 1);
            if($c == "+")
            {
                $n = substr($diff, 0, $i);
                $pre = substr($text, 0, $n);
                $post = substr($text, $n);
                return $pre.substr($diff, $i+1).$post;
            }
            elseif($c == "-")
            {
                $n = substr($diff, 0, $i);
                $pre = substr($text, 0, $n);
                $post = substr($text, $n);
                return $pre.substr($post, strlen($diff)-$i-1);
            }
        }
        return $text;
    }
    foreach($diff as $d)
    {
        $text = patch($text, $d);
    }
    return $text;
}

/**
* Undo patched changes to a string.
* $text: Final string
* $diff: Changes that were applied
* return: Unpatched string
*/
function unpatch($text, $diff)
{
    if(!is_array($diff))
    {
        $n = 0;
        for($i=0; $i<strlen($diff); $i++)
        {
            $c = substr($diff, $i, 1);
            if($c == "-")
            {
                $n = substr($diff, 0, $i);
                $pre = substr($text, 0, $n);
                $post = substr($text, $n);
                return $pre.substr($diff, $i+1).$post;
            }
            elseif($c == "+")
            {
                $n = substr($diff, 0, $i);
                $pre = substr($text, 0, $n);
                $post = substr($text, $n);
                return $pre.substr($post, strlen($diff)-$i-1);
            }
        }
        return $text;
    }
    foreach(array_reverse($diff) as $d)
    {
        $text = unpatch($text, $d);
    }
    return $text;
}


// list($old,$new)=show_diff($old,$new);
function show_diff($old,$new) {

	$newpre='<span style="background:green">';
	$newpost='</span>';
	$oldpre='<span style="background:red">';
	$oldpost='</span>';

	if($old!=$new) {
		$Change=diff($old,$new);
		$newcounter=0;
		$oldcounter=0;
		foreach($Change as $patch) {
			preg_match("/^(\d*)([+-])(.*)$/ms",$patch,$matches);
			$pos=$matches[1];
			$mode=$matches[2];
			$string=$matches[3];
			if($mode=="-") {
				$pos+=$oldcounter;
				$old=substr($old,0,$pos).$oldpre.substr($old,$pos,strlen($string)).$oldpost.substr($old,$pos+strlen($string));
				$oldcounter+=strlen($oldpre.$oldpost);
				$oldcounter+=strlen($string);
			} else {
				$pos+=$newcounter;
				$new=substr($new,0,$pos).$newpre.substr($new,$pos,strlen($string)).$newpost.substr($new,$pos+strlen($string));
				$oldcounter-=strlen($string);
				$newcounter+=strlen($newpre.$newpost);
			}
		}
	}
	return array($old,$new);
}

?>
