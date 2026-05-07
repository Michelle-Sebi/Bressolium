function Badge({ count, style: customStyle, ...props }) {
    if (!count) return null;

    return (
        <span
            className="ui-badge"
            style={{
                fontSize:        '11px',
                fontWeight:      'bold',
                color:           '#fff',
                backgroundColor: '#458B74',
                padding:         '1px 5px',
                minWidth:        '18px',
                textAlign:       'center',
                display:         'inline-block',
                ...customStyle,
            }}
            {...props}
        >
            {count}
        </span>
    );
}

export default Badge;
