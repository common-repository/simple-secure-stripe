import RadioControlOption from '../radio-option';
import classnames from 'classnames';

export const RadioControlAccordion = ({option, checked, onChange}) => {
    const {label, value} = option;
    return (
        <div className='sswps-blocks-radio-accordion'>
            <RadioControlOption checked={checked} onChange={onChange} value={value} label={label}/>
            <div
                className={classnames('sswps-blocks-radio-accordion__content', {
                    'sswps-blocks-radio-accordion__content-visible': checked
                })}>
                {option.content}
            </div>
        </div>

    )
}

export default RadioControlAccordion;