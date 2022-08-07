<?php

function sampling($chars, $size, $combinations = array())
{
    $starttime = time();
    if (empty($combinations)) {
        $combinations = $chars;
    }

    # we're done if we're at size 1
    if ($size == 1) {
        return $combinations;
    }

    # initialise array to put new values in
    $new_combinations = array();

    # loop through existing combinations and character set to create strings
    foreach ($combinations as $combination) {
        foreach ($chars as $char) {
            $combined = $combination . strtoupper($char);
            if (!in_array($combined, $new_combinations)) {
                $new_combinations[] = $combination . strtoupper($char);
            }
            if (count($new_combinations) > 500) {
                return sampling($chars, $size - 1, $new_combinations);
            }
        }
    }

    # call same function again for the next iteration
    return sampling($chars, $size - 1, $new_combinations);
}

function sub_category_id($first_two, $result)
{
    if (in_array($first_two, $result)) {
        $sub_cateogory_id = $first_two;
    } else {
        $key = array_rand($result);
        $sub_cateogory_id = $result[$key];
    }
    return $sub_cateogory_id;
}

function search_category($combination, $existing_ids, $first_two, $sampling_count)
{
    $sampled_compinations = sampling($combination, $sampling_count);
    $result = array_diff($sampled_compinations, $existing_ids);
    if (count($result) > 0) {
        return sub_category_id($first_two, $result);
    } else {
        return 'false';
    }
}

function clean($string)
{
    $string = str_replace(" ", "", $string);
    $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
    $string = str_replace("-", "", $string);
    return $string;
}

function sampling_category($combination, $existing_ids, $article_name, $first_two, $sampling_count)
{

    $sub_cateogory_id = search_category($combination, $existing_ids, $first_two, $sampling_count);
    if ($sub_cateogory_id == 'false') {
        $first_letter = strtoupper($article_name[0]);
        $second_letter = strtoupper($article_name[1]);
        if ($sampling_count == 3) $third_letter = strtoupper($article_name[3]);
        $first_two = $first_letter . $second_letter;
        if ($sampling_count == 3) $first_two = $first_letter . $second_letter . $third_letter;
        if ($sampling_count == 1) $first_two = $first_letter;
        $letters[0] = $first_letter;
        if ($sampling_count > 1) $letters[1] = $second_letter;
        if ($sampling_count > 2) $letters[2] = $third_letter;
        $sub_cateogory_id =  search_category($letters, $existing_ids, $first_two, $sampling_count);

        if ($sub_cateogory_id == 'false') {
            $article_name_clean = clean($article_name);
            $article_name_split = str_split(strtoupper($article_name_clean));
            if (is_array($article_name_split)) {
                $article_name_split = array_filter($article_name_split);
            }

            $sub_cateogory_id = search_category($article_name_split, $existing_ids, '', $sampling_count);
            if ($sub_cateogory_id == 'false') {
                $alphabets = "A B C D E F G H I J K L M N O P Q R S T U V W X Y Z 1 2 3 4 5 6 7 8 9 0";
                if ($sampling_count == 1) {
                    $alphabets = "A B C D E F G H I J K L M N O P Q R S T U V W X Y Z";
                }
                $alphabets_explode = explode(" ", $alphabets);
                $sub_cateogory_id =  search_category($alphabets_explode, $existing_ids, '', $sampling_count);
            }
        }
    }
    return $sub_cateogory_id;
}
$words = preg_replace('/[0-9]+/', '', 'ABCD12');
$random_letter = sampling_category(array('A'),array('AB'), 'ABCD', 'A', 1);
//$random_letter = sampling_category(array('A','B','C','D'),array('AB'), 'ABCD', 'AB', 2);
