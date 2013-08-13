yii-spatialdata-behavior
========================

SpatialDataBehavior allows to interact with spatial-fields of AR-model like regular arrays

If your MySql database contains Spatial-fields (eg POINT or LINESTRING), this behavior allows to operate with these fields in models as normal arrays:
~~~
[php]
$spatial=new SpatialTableModel;
$spatial->pointField = array(86.18, 55.31);
$spatial->linestringField = [[86.18, 55.31], [86.19, 55.32]];
$spatial->multiLineStringField = [[[86.18, 55.31], [86.19, 55.32]], [[85.18, 56.31], [87.19, 54.32]]];
$spatial->save();
~~~

All "instantiable" class are supported except GeometryCollection (see [MySql's Spatial Extention docs](http://dev.mysql.com/doc/refman/5.6/en/gis-geometry-class-hierarchy.html)).

Behaviour also converts the data into an array when received from the base:

~~~
[php]
$spatial=SpatialTableModel::model()->findByPk(1);

$yCoord=$spatial->pointField[1];
$secondPointYCoord=$spatial->linestringField[1][1];
$secondLineSecondPointYCoord=$spatial->multyLineStringField[1][1][1];
~~~

##Requirements

Developed with Yii 1.1.13. Should work with any 1.1 Version of Yii.

##Usage

1. Place SpatialDataBehavior.php into your project, for example to the folder components/behaviors
2. Register the behavior on the model with spatial fields
3. Specify a list of your spatial fields:

~~~
[php]
public function behaviors() {
	return array(
			'spatial'=>array(
				'class'=>'application.components.behaviors.SpatialDataBehavior',
				'spatialFields'=>array(
					'pointField',
					'lineStringField',
					'poligonField',
					'multipointField',
					'multiLineStringField',
					'multiPolygonField'
				),
			)
	);
}
~~~

Use behavior as described above.

You can also use the method `arrayToGeom` to convert an array to OpenGIS's WKT format which used by MySql:
~~~
[php]
$findByPoint=SpatialTest::model()->find('point=GeomFromText(:data)',array(':data'=>"Point(".SpatialTest::model()->arrayToGeom(array(86.18,55.31)).")"));
~~~

##Resources

* [Extention page on gitHub](https://github.com/UncleCoder/yii-spatialdata-behavior)

