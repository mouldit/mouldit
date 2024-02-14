<h1>Mouldit</h1>
<p>Mouldit strives to make creating enterprise ready CRUD applications easier and faster at the same time. It does this with the help of a CLI, which let's you setup all necessary rest API's together with the UI that needs to interact with them. The configuration path you follow through this CLI will make it possible to get as custom as you need, not only regarding your rest API's but regarding the frontend of your application as well. At the same time for every configuration, default behaviour is built-in as to limit the specifications you need to make.</p>
<h2>The Mouldit CLI</h2>
<p>With the CLI you can configure the backend as well as the frontend of your application. When you have ended the steps in the CLI, Mouldit will generate you application based on this configuration as well as on the database schema you have manually added to your project (see the section about Database schema for more details).</p>
<h2>Principle</h2>
<p>Via the Mouldit CLI you can configure your crud actions as well as the entire frontend of your application. The CLI will infer all possible crudactions from your schema, or as for now at least all implemented actions (see list below). The configuration of such an action is explained in detail below. After all actions are configured the Mouldit CLI will guide you through the configuration of the frontend. This configuration consists of two parts. First you specify general things about your frontend, like navigation and pages. Then you will configure all UI components and their behaviour, which include triggering the above mentioned crudactions and dealing with their results. The frontend of course is optional, since it's perfectly possible you want API endpoints and expose them to external clients only. At the end the CLI will generate rest API's based on the configuration of your crudactions and a frontend if you have configured one.</p>
<h3>Rest API's</h3>
For now the generated API's will be <i>Express.js</i> based Rest API's. In the future it will be possible to opt for <i>est.js</i> based Rest API's as well. In some cases you might want to refine them yourself. This is typically the case when you deal with calculated fields, or nested queries. You will have to write edgeQL based queries for that and this is usually more convenient to do this yourself than via the CLI. Of course in the future this usecases will be possible through configuration alone, but even then you still might want to do it manually. In any case Mouldit will always leave you the choice.
<h3>UI component library</h3>
<h2>Gradual approach</h2>
<p>Although the goal is to make the CLI so that you don't need to add any custom code after the initial setup, this will only be achieved gradually. As Mouldit grows the amount of (crud)actions will get bigger as well as the level of detail to which you can configure these actions. For the frontend configuration the set of UI components will get bigger over time as well as the level of detail you can go to customize their appearance and behaviour.</p>
<h3>Database schema</h3>
Mouldit only works with an edgeDB project. It will use the types that can be found in your edgeQL schema to determine all possible server actions. <a href="https://www.edgedb.com">Here</a> you can find information on how to create an edgeDB project and the necessary schema.
<h3>Server actions</h3>
<p>These type of actions represent all rest API endpoints that a client can send a request to. Each server action represents one rest API. At the moment the following type of actions are possible (a lot more will be added in the future!):
<ul>
 <li>Get one</li>
 <li>Get all</li>
 <li>Add one to list</li>
 <li>Remove one from list</li>
</ul>
All these actions represent either a query or a mutation. Queries are all actions that starts with 'Get'.
</p>
<h4>Queries</h4>
<p>A query represents a get request. A query has five parts you can configure:
<ul>
 <li>concept</li>
 <li>filter</li>
 <li>exclude/include (sub)field(s)</li>
 <li>transform fieldvalue(s)</li>
 <li>calculated (sub)field(s)</li>
</ul>
</p>
<h5>Concept</h5>
<p>
 This is the resource that will be fetched. In your edgeQL schema it is coded as follows:
 
```
  type Account {
    required username: str {
      constraint exclusive;
    };
    multi watchlist: Content;
  }

  type Person {
    required name: str;
    link filmography := .<actors[is Content];
  }
```

In the example above <i>Account</i> en <i>Person</i> are concepts. You can instead of typing a specific concept also type <i>this</i>. That is how you can refer to the current record when your query is part of a calculation (see below).
</p>
<h5>Filter</h5>
<p>
 Here you can configure at which conditions fetched records need to apply by specififying the values of the different fields within a record.
</p>
<h5>Field configurations</h5>
<p>
 You can specify wich fields to include or exclude, how to transform the value of a specific field before sending it to the client or add a calculated field. When prompted to enter fields to include/exclude you type their names separated by comma's. Transforming values is a feature that will be added later. 
</p>
<h6>Calculated field</h6>
<p>
 For a calculated field you specify its name, and its type, for instance a boolean. For Mouldit to know how to calculate its value, you have to specify the type of calculation you want to perform. At the moment there are only two calculations. Of course a lot more will be added in the future, according to the gradual approach as explained in the section below. The current types are:
 <ul>
  <li><i>Checking for equality</i>: You have to specify two parameters which will be checked for equality. For now these parameters can be either primitive values like a string, a number, a boolean,... or another calculation that when executed will result in such a primitive value. Mouldit will make sure only primitives of the same type will be compared. Therefor only valid calculations can be added as parameter. </li>
  <li><i>Counting the number of fetched records of a particular query</i>: You pass it a query configuration, which will result in the number of records that query comes up with.</li>
 </ul>
</p>
<h4>Mutations</h4>
A mutation accepts a concept, a filter and a return query action as configuration parameters. Each of these is explained in detail in the section above.
<h2>Example</h2>
<p>For now we a very simple application, where in the frontend we have one menu item "movies". When the user clicks on this item, the frontend must show different card components where each card shows the details of the movie, namely its title and its release year. In each card component we want a button to add the movie to our watchlist or if it's already added we want to have a remove button that let's us remove the movie from our watchlist.
We therefore start with the following schema:</p>

```
module default {

  type Account {
    required username: str {
      constraint exclusive;
    };
    multi watchlist: Content;
  }

  type Movie{
    required title: str;
    release_year: int32;
  }
  
};
```

<h5>Server action configuration</h5>
<p>When we start the CLI (<i>how?</i>) it will ask for all the queries and mutations you want for each of the concepts in this schema. First it will ask if we want queries and mutations for the Person concept. Since we only want the frontend to show all movies, we enter N. Then we press enter as to confirm we do want queries and mutations for the Movie concept.</p>
<p>Next we are asked if we want all standard queries and mutations (<i>which are?</i>). We type N since we only want one specific query and two specific mutations.</p>
<p>Then we can enter the specific queries we want. We select GET ALL, since we need all movies in our frontend.</p>
<p>Now we can configure our GET ALL query. 
<ol>
 <li>It will ask if we want a filter. We just enter here since the default is No and we want all movies not a selection of movies.</li>
 <li>Next it will ask us whether we want certain fields excluded (1) or included (2). We enter here since we want all field of our Movie concept.</li>
 <li>At last it will ask us whether we need calculated fields. We type Y since we do. We need a calculated field that tells the frontend whether a movie is in my watchlist or not. The type of this calculated field will therefore be a boolean. Based on this boolean value the frontend will then either show a button to add the movie to my watchlist or a button to remove the movie from my list.</li>
 <li>Next we have to give the name of our calculated field. We type <i>isInList</i></li>
 <li>Next we have to select the type of the field. We select <i>Boolean</i></li>
 <li>Next we have to select our root calculation. We select <i>Check for equality</i></li>
 <li>Next we have to enter the two parameters this calculation expects. First we have to give the type. For the first parameter we select <i>Calculation</i></li>
 <li>Next we have to give the details of the calculation from the previous step. First we need to select the type of calculation. We select <i>Count all records</i></li>
 <li>Just as with our root calculation we need to specify the parameters. For the first parameter we select <i>Query</i>. We want to check how many movie records in our watchlist have the same id as the current movie in our fetched list of movies. </li>
 <li>We now configure our query. Since we are pretending there is only one user and this user has username 'Pol' we will fetch the watchlist of this user. First we will select the type of query <i>GET ONE</i>.</li>
 <li>Then we select as concept <i>watchlist</i></li>
 <li> Next we choose Y for filter and select <i>username</i> and type <i>Pol</i>.</li>
 <li>Next we select <i>Current record</i> for the second parameter of the calculation <i>Count all records</i></li>
 <li>As a last step we have to enter the second parameter for our root calculation. We select as a type <i>Number</i> and for the value we type <i>1</i></li>
</ol>
</p>
<p>The result of this configuration is a rest API in the backend that will return all movies with as an extra field a boolean which indicates if it is in the list of the account with username <i>Pol</i>. Next the CLI will ask you how the frontend needs to be setup that make use of this action. </p>
<h5>Frontend configuration</h5>
<p>Frontend configuration has tree main parts:
<ul>
 <li>General configuration</li>
 <li>Configuration that connects serveractions with frontend components</li>
 <li>Configuration that connects useractions with frontend components</li>
</ul>
It's important to understand that we can configure frontend components differently according to screensize. But we don't have to. All components have default behaviour that makes them look good on anyt screen. Only when you need custom layout and style depending on the screensize you can do this. Configuring UI components for different screensizes is handled after the general configuration of each component. (<i>How?</i>)
</p>
<ol>
 <li>The CLI starts with general configuration be selecting the type of menu you would want. For now there is only one option so we select this option: <i>Menubar</i></li>
<li>Next it asks to connect this component with all main concepts from our schema. We type N, since we only want a menu item for the movie concept.</li>
<li>Then we can select all concepts we do want a menu item for. We select <i>Movie</i></li>
</ol>
<p>That is all for general configuration. (<i>What other general configuration could be useful?</i>)</p>
<p>Next  it will ask for connecting frontend with serveractions.
Each time a serveraction was configured the CLI will ask you whether you need frontend configuration for this. Since we do we type Y.</p>
<p>A serveraction has a result. Configuring the connection of a serveraction to the UI is equivalent with telling what to do with the result of this action in the frontend.</p>
<ol>
 <li>The result of our previously configured action will be an array of movies, with a special property <i>isInList</i>. (<i>How a bad request has to be configured?</i>) Therefore the CLI will ask to select the component we want to use to render this list of movies. We select <i>Cards</i> which is a list of cards, one card component for each movie.</li>
 <li>Next, since it is a list of cards it will ask us how to configure such a card. It starts with asking which fields need to be connected with which component properties. For the <i>title</i> field we select the <i>title</i> render property. For the <i>release_year</i> field we select the <i>subtitle</i> render property.</li>
 <li>We then type A to abort further field configuration. So then the CLI asks to inject components into the card component. We select <i>footer</i> since we want a button in there. So now we select <i>Buttons</i> as the component to be injected.</li>
 <li>Next it will ask us to configure this <i>Buttons</i> component. Again it starts with connecting data properties with render properties. This time it's a little more complicated. Depending on the value of<i>isInList</i> we need Mouldit to render a different kind of button, which is why we have selected a <i>Buttons</i> and not <i>Button</i>. Since there is no server resulting data list to base the number of Button components on the CLI asks us the number of buttons. We type 2 since there are only 2 types of buttons we want to render in each card.</li>
 <li>Next it will start the configuration of button 1. We link the <i>isInList</i> field property to the <i>visible</i> render property of the button. It will ask (<i>do not forget to add this step in the previous component configuration</i>) if we want to use the value as is or whether we want to manipulate it first. We type Y because we want to reverse the boolean value of this field property first. So in the next step we select the Reverse data manipulation function.</li>
 <li>Next we type A because that is concerning matching fields with render properties. </li>
 <li>Next we can fill in values for the other properties. We select <i>Label</i> and type <i>add</i> to indicate that this button should be used to add the movie to Pol's list.</li>
 <li>We type A to abort the configuration of Button 1</li>
 <li>For Button 2 we do the same configuration, exept we do not use any data manipulation for the isInList field property. For the <i>Label</i> render property we type <i>Remove</i></li>
</ol>

