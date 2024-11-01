import classnames from 'classnames';

export const RadioControlOption = ({checked, onChange, value, label}) => {
    return (
        <label
            className={classnames('sswps-blocks-radio-control__option', {
                'sswps-blocks-radio-control__option-checked': checked
            })}>
            <input
                className='sswps-blocks-radio-control__input'
                type='radio'
                value={value}
                checked={checked}
                onChange={(event) => onChange(event.target.value)}/>
            <div className='sswps-blocks-radio-control__label'>
                <span>{label}</span>
            </div>
        </label>
    )
}

export default RadioControlOption;