function IconTile({ src, alt, size = '60%' }) {
    return (
        <img
            src={src}
            alt={alt}
            style={{ width: size, height: size, objectFit: 'contain' }}
        />
    );
}

export default IconTile;
