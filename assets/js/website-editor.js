(() => {
  'use strict';
  const form = document.querySelector('[data-content-editor]');
  if (!form) return;
  const config = JSON.parse(form.querySelector('[data-editor-config]').textContent);
  let data = config.data;
  let addressEdited = false;
  const fallbackAddress = `post-${Math.floor(Date.now()/1000).toString(36)}`;
  const output = form.querySelector('[name="payload"]');
  const container = form.querySelector('[data-editor-fields]');
  const upload=form.querySelector('[name="images[]"]');
  const multipleImages=config.collection==='published' || data.type==='card series';
  const validateUploads = () => {
    if (!upload) return;
    const count=multipleImages ? (data.cards?.length||0)+upload.files.length : upload.files.length;
    upload.setCustomValidity(count>(multipleImages?10:1) ? (multipleImages?'A post can contain at most 10 images.':'Choose one image.') : '');
  };
  let number = 0;
  const hiddenKeys = new Set(['legacy_ids','pdf_path','word_count','reading_time_minutes','published_at']);
  const label = key => ({cards:'Gallery images (up to 10)',video_urls:'YouTube videos (optional)',article_body:'Article text (optional)',slug:'Page address',alt:'Image description',credit_note:'Credits',publication_status:'Visibility',date_precision:'Date precision',source_note:'Source access note'}[key] || key.replaceAll('_',' ').replace(/^./,c=>c.toUpperCase()));
  const set = (path,value) => {let target=data;for(let i=0;i<path.length-1;i++)target=target[path[i]];target[path.at(-1)]=value; output.value=JSON.stringify(data);};
  const blank = value => Array.isArray(value) ? [] : value && typeof value==='object' ? Object.fromEntries(Object.entries(value).map(([key,child])=>[key,blank(child)])) : typeof value==='number' ? 0 : typeof value==='boolean' ? false : '';
  const makeButton = (text,action) => {const button=document.createElement('button');button.type='button';button.textContent=text;button.addEventListener('click',action);return button;};
  const render = () => {
    number=0;container.replaceChildren(); Object.entries(data).forEach(([key,value])=>{if(!hiddenKeys.has(key)) container.append(field(value,[key],key));});
    if (config.collection==='skills') {
      const group=document.createElement('div');group.className='input-group';const caption=document.createElement('label');caption.htmlFor='new-skill-category';caption.textContent='New skill category';const input=document.createElement('input');input.id='new-skill-category';
      const add=makeButton('Add category',()=>{const key=input.value.trim();if(!key || key.length>60 || ['__proto__','prototype','constructor'].includes(key) || Object.hasOwn(data,key)) {input.setCustomValidity('Choose a new category name.');input.reportValidity();return;}data[key]=[];render();});add.className='cms-add';input.addEventListener('input',()=>input.setCustomValidity(''));group.append(caption,input,add);container.append(group);
    }
    output.value=JSON.stringify(data);validateUploads();
  };
  const field = (value,path,key) => {
    if (Array.isArray(value)) {
      const group=document.createElement('fieldset');group.className='cms-group';const legend=document.createElement('legend');legend.textContent=label(key);group.append(legend);
      value.forEach((child,i)=>{
        const row=document.createElement('div');row.className='cms-array-row';const entry=document.createElement('div');entry.className='cms-row-value';entry.append(field(child,[...path,i],typeof child==='object' ? `Entry ${i+1}` : `Item ${i+1}`));
        if (key==='cards' && typeof child==='string' && /^(assets\/images|uploads)\//.test(child)) {const img=document.createElement('img');img.src=config.imageBase+child;img.alt=`Image ${i+1} preview`;img.className='cms-image-preview';entry.prepend(img);}
        const actions=document.createElement('div');actions.className='cms-array-actions';
        const up=makeButton('↑',()=>{[value[i-1],value[i]]=[value[i],value[i-1]];render();});up.disabled=i===0;up.setAttribute('aria-label',`Move item ${i+1} up`);
        const down=makeButton('↓',()=>{[value[i+1],value[i]]=[value[i],value[i+1]];render();});down.disabled=i===value.length-1;down.setAttribute('aria-label',`Move item ${i+1} down`);
        const remove=makeButton('Remove',()=>{value.splice(i,1);render();});remove.setAttribute('aria-label',`Remove ${key} item ${i+1}`);
        actions.append(up,down,remove);row.append(entry,actions);group.append(row);
      });
      const add=makeButton(key==='cards'?'Add existing image':key==='video_urls'?'Add YouTube video':'Add item',()=>{value.push(config.rowTemplates[key] ? structuredClone(config.rowTemplates[key]) : value.length ? blank(value[0]) : '');render();});add.className='cms-add';add.disabled=['cards','video_urls'].includes(key) && value.length>=10;group.append(add);return group;
    }
    if (value && typeof value==='object') {
      const group=document.createElement('fieldset');group.className='cms-group';const legend=document.createElement('legend');legend.textContent=label(key);group.append(legend);Object.entries(value).forEach(([childKey,child])=>{if(!hiddenKeys.has(childKey))group.append(field(child,[...path,childKey],childKey));});return group;
    }
    const group=document.createElement('div');group.className='input-group';const caption=document.createElement('label');const id=`cms-field-${++number}`;caption.htmlFor=id;caption.textContent=label(key);group.append(caption);
    let input;
    const options={publication_status:['draft','published'],date_precision:['year','month','day'],type:['card series','photograph','video']};
    if (options[key]) {input=document.createElement('select');options[key].forEach(option=>{const element=document.createElement('option');element.value=option;element.textContent=option==='card series'?'ANCHOR / gallery post':option;input.append(element);});}
    else if (typeof value==='boolean') {input=document.createElement('input');input.type='checkbox';input.checked=value;}
    else if (/description|article_body|summary|intro|about|philosophy|note|learning|contribution/.test(key) || String(value??'').length>180) {input=document.createElement('textarea');input.rows=key==='article_body'?18:4;}
    else {input=document.createElement('input');input.type=typeof value==='number'?'number':'text';}
    input.id=id;input.value=value??'';input.dataset.fieldKey=key;
    if(path.length===1 && ['title','slug'].includes(key)) input.required=true;
    if (key==='type' || (key==='slug' && config.existing)) {input.readOnly=true;if(input.tagName==='SELECT')input.disabled=true;}
    input.addEventListener('input',()=>{
      set(path,typeof value==='boolean'?input.checked:typeof value==='number'?Number(input.value):value===null && input.value===''?null:input.value);
      if (!config.existing && path.length===1 && key==='slug') addressEdited=true;
      if (!config.existing && path.length===1 && key==='title' && !addressEdited) {
        let address=input.value.normalize('NFKD').replace(/[\u0300-\u036f]/g,'').toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
        if(!address) address=fallbackAddress; else if(!/^[a-z]/.test(address)) address='post-'+address;
        address=address.slice(0,100).replace(/-$/,'');set(['slug'],address);
        const addressField=form.querySelector('[data-field-key="slug"]');if(addressField)addressField.value=address;
      }
    });
    group.append(input);
    if (key==='article_body') {const help=document.createElement('p');help.className='admin-help';help.textContent='Full post writing. Use paragraphs, ## headings, - lists, **bold** text and links. Images and videos appear above it.';group.append(help);}
    return group;
  };
  render();
  upload?.addEventListener('change',validateUploads);
  form.addEventListener('submit',()=>{output.value=JSON.stringify(data);});
})();
