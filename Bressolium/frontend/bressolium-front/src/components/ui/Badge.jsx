function Badge({ count }) {
    if (!count) return null;

    return (
        <span
            style={{
                fontSize:        '11px',
                fontWeight:      'bold',
                color:           '#fff',
                backgroundColor: '#458B74',
                padding:         '1px 5px',
                minWidth:        '18px',
                textAlign:       'center',
                display:         'inline-block',
            }}
        >
            {count}
        </span>
    );
}

export default Badge;
