<?php


$template_content = [
    'Singlelines' => [
        [
            'Content' => get_the_title(),
            'Href' => get_the_permalink(),
        ],
    ],
    'Multilines' => [
        [
            'Content' => $campaign_data['CMREST_email_text'],
        ],
        [
            'Content' => '<div><!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="' . get_the_permalink() . '" 
                               style="height:40px;v-text-anchor:middle;width:200px;" arcsize="10%" strokecolor="' . $this->CMREST_campaign_settings['CMREST_campaign_button_color'] . '" 
                               fillcolor="' . $this->CMREST_campaign_settings['CMREST_campaign_button_color'] . '">
                              <w:anchorlock/><center style="color:#ffffff;font-family:sans-serif;font-size:13px;font-weight:bold;">' . $this->CMREST_campaign_settings['CMREST_campaign_button_text'] . '</center></v:roundrect>
                              <![endif]--><a href="' . get_the_permalink() . '" style="background-color:' . $this->CMREST_campaign_settings['CMREST_campaign_button_color'] . ';
                              border:1px solid ' . $this->CMREST_campaign_settings['CMREST_campaign_button_color'] . ';
                              border-radius:4px;color:#ffffff;display:inline-block;
                              font-family:sans-serif;font-size:13px;font-weight:bold;
                              line-height:40px;text-align:center;text-decoration:none;width:200px;
                              -webkit-text-size-adjust:none;mso-hide:all;">' . $this->CMREST_campaign_settings['CMREST_campaign_button_text'] . '</a></div>',
        ],
        [
            'Content' => $this->CMREST_campaign_settings['CMREST_campaign_footer_explain']
        ],
        [
            'Content' => $this->CMREST_campaign_settings['CMREST_campaign_footer_terms']
        ]

    ],
];
if (has_post_thumbnail()) {
    $template_content['Images'] = [
        [
            'Content' => get_the_post_thumbnail_url(null, 'large'),
            'Alt' => get_the_post_thumbnail_caption(),
            'Href' => get_the_permalink(),
        ],
    ];
}

return $template_content;