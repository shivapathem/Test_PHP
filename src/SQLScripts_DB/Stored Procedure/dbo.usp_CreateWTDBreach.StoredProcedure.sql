USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_CreateWTDBreach]    Script Date: 3/24/2026 9:26:30 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER               PROCEDURE  [dbo].[usp_CreateWTDBreach]
@pStartDate                DATE,
@pEndDate                  DATE,
@pteamId			       INT,
@pNetLogin                 VARCHAR(30) = NULL,
@pSchedulingPersonID       INT = NULL

AS
BEGIN

    SET NOCOUNT ON

    SET DATEFORMAT YMD

	DECLARE  @vWeekNumber               INT = 0,
			 @vStartWeek                INT = 0,
			 @vEndWeek                  INT = 0,
			 @vminwtdweek               DATE,
			 @vmaxwtdweek               DATE,
			 @vname                     VARCHAR(100),
 			 @WTD_DutyName              NVARCHAR(100),
 			 @WTD_DutyDate              DATE,
			 @LeaveDutyStartDate        DATE,
			 @LeaveDutyEndDate          DATE,
			 @HDate                     VARCHAR(100),
			 @schedulingpersonid        INT,
			 @LeaveChkStartdate         DATE,
			 @LeaveChkEnddate           DATE,
			 @vuserID					INT,
			 @workTimeDirectiveOptOut   TINYINT,
			 @checkOverSixDaysWorked    TINYINT;

	
    BEGIN TRY    

	    SET @HDate = FORMAT(Getdate(),'HH:mm') + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy')
		
		DECLARE @TempAllocationsWTD TABLE ( schedulingpersonid INT,
											weeknumber INT,
											duration INT,
											starttime INT,
											endtime INT,
											dutydate DATE,
											iDay INT,
											DutyName NVARCHAR(100))	

		DECLARE @AllocationBreachData TABLE ( SchedulingTeamId INT,
											  SchedulingPersonID INT, 
											  breachtype INT,
											  startdate DATE,
											  enddate DATE, 
											  BreachedBy VARCHAR(100),
											  BreachedDate DATETIME,
											  IsApproved INT,
											  History VARCHAR(250))		

        DECLARE @AllocationBreachDataTemp TABLE ( SchedulingPersonID INT,
                                                  WeekNumber INT,
												  StartDate DATE,
												  EndDate DATE,
												  WeekTotal FLOAT)	
												  
		DECLARE @AllocationBreachDataTP TABLE ( SchedulingTeamId INT,
        									  SchedulingPersonID INT, 
											  breachtype INT,
											  startdate DATE,
											  enddate DATE, 
											  BreachedBy VARCHAR(100),
											  BreachedDate DATETIME,
											  IsApproved INT,
											  History VARCHAR(250))													  		   
	
      SELECT  @vname = ud.UD_DisplayName,
	          @vuserID = ud.UD_UserID
	    FROM UserDetails ud (nolock)
	   WHERE ud.UD_NetLogin = @pNetLogin


	   SELECT @workTimeDirectiveOptOut=workTimeDirectiveOptOut ,
			  @checkOverSixDaysWorked=checkOverSixDaysWorked
         FROM schedulingTeams  AS ST WITH(NOLOCK)
         WHERE ST.schedulingTeamId=@pteamId 

         INSERT INTO @TempAllocationsWTD  ( schedulingpersonid,
											weeknumber,
											duration,
											starttime,
											endtime,
											dutydate,
											iDay,
											DutyName)			
		 SELECT AP.ASP_SchedulingPersonID,
				AL.AL_WeekNumber,
				AD.AD_Duration,
				AD.AD_StartTimeSec,
				AD.AD_EndTimeSec,
				AD.AD_DutyDate,
				AD.AD_iDay,
				CASE WHEN AD_DutyName IS NULL THEN 'U'
					WHEN AD_DutyType IN (8,11,12)
					     THEN CASE WHEN ASP_LeaveType = 1
								   THEN 'Leave'
								   WHEN ASP_LeaveType = 2
								   THEN 'OFF Leave'
								   WHEN ASP_LeaveType = 3
								   THEN 'Sick'
								   WHEN ASP_LeaveType = 4
								   THEN 'U-Sick'
								   WHEN ASP_LeaveType = 5
								   THEN '-Sick'
								   WHEN ASP_LeaveType = 7
								   THEN 'Absent'
							   END
			        ELSE AD_DutyName
				END		  			      AS DutyName
		   FROM Allocations AL
		   INNER JOIN TimeDimension TD ON AL.AL_WeekNumber = TD.ixYearWeek 
		   INNER JOIN AllocationsScheduledPersons AP on AL.AL_AllocationsID = AP.ASP_AllocationsID
													AND TD.ixDayInWeek = AP.ASP_iDay
		   INNER JOIN AllocationsDuties AD on AD.AD_AllocationsDutyID = AP.ASP_AllocationsDutyID 
		  WHERE AL.AL_SchedulingTeamID = @pteamId
			AND TD.dDateTime between DATEADD (DAY, -21, @pStartDate) AND DATEADD(DAY,21,@pEndDate)
			AND AP.ASP_SchedulingPersonID = ISNULL(@pSchedulingPersonID,AP.ASP_SchedulingPersonID)
			AND AL.AL_Status <> 9
		 UNION ALL
		 SELECT AP.ASP_SchedulingPersonID,
				AL.AL_WeekNumber,
				AD.AD_Duration,
				AD.AD_StartTimeSec,
				AD.AD_EndTimeSec,
				AD.AD_DutyDate,
				AD.AD_iDay,
				CASE WHEN AD_DutyName IS NULL THEN 'U'
					WHEN AD_DutyType IN (8,11,12)
					     THEN CASE WHEN ASP_LeaveType = 1
								   THEN 'Leave'
								   WHEN ASP_LeaveType = 2
								   THEN 'OFF Leave'
								   WHEN ASP_LeaveType = 3
								   THEN 'Sick'
								   WHEN ASP_LeaveType = 4
								   THEN 'U-Sick'
								   WHEN ASP_LeaveType = 5
								   THEN '-Sick'
								   WHEN ASP_LeaveType = 7
								   THEN 'Absent'
							   END
			        ELSE AD_DutyName
				END		  			      AS DutyName
		   FROM Allocations AL
		   INNER JOIN TimeDimension TD ON AL.AL_WeekNumber = TD.ixYearWeek 
			INNER JOIN AllocationsAddPersons AA on AL.AL_AllocationsID = AA.AAP_AllocationsID
											AND TD.ixDayInWeek = AA.AAP_iDay
			INNER JOIN AllocationsScheduledPersons AP on AAP_AllocationsSPID = ASP_AllocationsSPID
			INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
			INNER JOIN Allocations ALA ON ALA.AL_AllocationsID = AD_AllocationsID
		  WHERE AL.AL_SchedulingTeamID = @pteamId
			AND TD.dDateTime between DATEADD (DAY, -21, @pStartDate) AND DATEADD(DAY,21,@pEndDate)
			AND AP.ASP_SchedulingPersonID = ISNULL(@pSchedulingPersonID,AP.ASP_SchedulingPersonID)
			AND AL.AL_Status <> 9
			AND ALA.AL_Status <> 9



           SELECT @LeaveChkStartdate = MAX(Dutydate)
		    FROM
		   ( SELECT TD.dDateTime as Dutydate, TWTD.DutyName as DutyName
		       FROM TimeDimension TD
			   LEFT JOIN @TempAllocationsWTD TWTD ON TWTD.dutydate = TD.dDateTime
			  WHERE TD.dDateTime between DATEADD (DAY, -21, @pStartDate) and @pStartDate
			 ) FD WHERE DutyName IS NULL

           SELECT @LeaveChkEnddate = MIN(Dutydate)
		    FROM
		   ( SELECT TD.dDateTime as Dutydate, TWTD.DutyName as DutyName
		       FROM TimeDimension TD
			   LEFT JOIN @TempAllocationsWTD TWTD ON TWTD.dutydate = TD.dDateTime
			  WHERE TD.dDateTime between @pEndDate and DATEADD(DAY,21,@pEndDate)
			 ) FD WHERE DutyName IS NULL
			
		     
		   SET @LeaveChkStartdate = CASE WHEN @LeaveChkStartdate IS NULL 
		                                 THEN DATEADD (DAY,-21,@pStartDate)
										 ELSE DATEADD (DAY,1,@LeaveChkStartdate) END

		   SET @LeaveChkEnddate = CASE WHEN @LeaveChkEnddate IS NULL 
		                               THEN DATEADD(DAY,21,@pEndDate)
									   ELSE DATEADD (DAY,-1,@LeaveChkEnddate) END


            INSERT INTO @AllocationBreachData( SchedulingTeamId,
											   SchedulingPersonID, 
											   breachtype,
											   startdate,
											   enddate, 
											   BreachedBy,
											   BreachedDate,
											   IsApproved,
											   History)			
			SELECT @pteamId as SchedulingTeamId,
				   fd.SchedulingPersonID as SchedulingPersonID, 
				   4 as breachtype,
				   fd.startdate as startdate,
				   fd.enddate as enddate, 
				   @vname as BreachedBy,
				   getdate() as BreachedDate,
				   CASE WHEN @workTimeDirectiveOptOut =1 THEN 1  WHEN
                          @checkOverSixDaysWorked=1 THEN 0 WHEN  
						  @checkOverSixDaysWorked =0 THEN 1
						  ELSE 0 END as IsApproved, 				   
				   'WTD Type 4 Breached By '+@vname+' '
				                   + CONVERT(VARCHAR, FORMAT(Getdate(),'HH:mm'), 108)
								   + ' On ' + CONVERT(VARCHAR, Getdate(), 103) as History	
			  FROM (
				  SELECT ST.SchedulingPersonID, 
						 st.DutyDate as startdate,
						 DATEADD(DAY,6,st.DutyDate) AS EndDate,
						 (SELECT count(1) 
							FROM @TempAllocationsWTD ET
						   WHERE ET.SchedulingPersonID = ST.SchedulingPersonID
							 AND et.DutyDate between st.DutyDate and DATEADD(DAY,6,st.DutyDate )
							 AND upper(ET.Dutyname) not in ('U-SICK','-SICK','ABSENT','U','OFF LEAVE')
							 AND ISNULL(ET.Duration,0) > 0
						  ) dutycount
					FROM @TempAllocationsWTD ST
				   WHERE upper(ST.Dutyname) not in ('U-SICK','-SICK','ABSENT','U','OFF LEAVE')
				    AND ISNULL(ST.Duration,0) > 0
					AND ST.DutyDate between DATEADD(DAY,-6, @pStartDate ) and DATEADD(DAY,6, @pEndDate ) 
				  ) FD where DutyCount > 6	  
		  
		
            INSERT INTO @AllocationBreachData( SchedulingTeamId,
											   SchedulingPersonID, 
											   breachtype,
											   startdate,
											   enddate, 
											   BreachedBy,
											   BreachedDate,
											   IsApproved,
											   History)	
			 select @pteamId as SchedulingTeamId,
				   fd.SchedulingPersonID as SchedulingPersonID, 
				   1 as breachtype,
				   cast(fd.startdate as date) as startdate,
				   cast(fd.enddate as date) as enddate, 
				   @vname as BreachedBy,
				   getdate() as BreachedDate,
				   Case when @workTimeDirectiveOptOut =1 then 1 else 0 END as IsApproved,
				   'WTD Type 1 Breached By '+@vname+' '
				                   + CONVERT(VARCHAR, Getdate(), 108)
								   + ' On ' + CONVERT(VARCHAR, Getdate(), 103) as History		   
			from (
			select TD.SchedulingPersonID,
				   TD.DutyDate   as StartDate,
				   DATEADD(DAY,1,TD.DutyDate) as EndDate, 
				   ( SELECT top 1 ( case when TD.EndTime <= ET.StartTime 
		                            AND TD.EndTime < TD.StartTime then ET.StartTime - TD.EndTime
								   when TD.EndTime > ET.StartTime then (86400-TD.EndTime ) + ET.StartTime end ) as duration
					   FROM @TempAllocationsWTD ET
					  WHERE ET.SchedulingPersonID = TD.SchedulingPersonID
						AND et.DutyDate = DATEADD(DAY,1,TD.DutyDate)
						AND NOT (ET.starttime=0 and ET.endtime=0)
						AND upper(ET.Dutyname) not in ('U-SICK','SICK','-SICK','LEAVE','ABSENT','U','OFF LEAVE')
				   ) AS Duration
			  from @TempAllocationsWTD  TD
			  WHERE TD.DutyDate between DATEADD(DAY,-1, @pStartDate ) and DATEADD(DAY,1, @pEndDate )
			    AND NOT (TD.starttime=0 AND TD.endtime=0)
				AND upper(TD.Dutyname) NOT IN ('U-SICK','SICK','-SICK','LEAVE','ABSENT','U','OFF LEAVE')
					) FD where CAST(isnull(Duration,40000) AS FLOAT) / CAST(3600 AS FLOAT) < 11 

	BEGIN
	
       DECLARE CUR_WTD CURSOR FOR 
        SELECT DutyName, DutyDate, schedulingpersonid
		  FROM @TempAllocationsWTD
		 WHERE DutyName IN ('LEAVE','-')--,'OFF LEAVE') 
		   AND dutydate between @LeaveChkStartdate and @LeaveChkEnddate
		 ORDER BY dutydate

     OPEN CUR_WTD

     FETCH NEXT FROM CUR_WTD INTO @WTD_DutyName, @WTD_DutyDate, @schedulingpersonid

      WHILE @@FETCH_STATUS = 0
	   BEGIN
	    IF ( UPPER(@WTD_DutyName) IN ('LEAVE','-'))--,'OFF LEAVE')  )
		 BEGIN
		   
		    SELECT @LeaveDutyEndDate = MIN(DutyDate) 
			  FROM (
						SELECT CASE WHEN TW.DutyName IS NULL THEN 'U' ELSE TW.DutyName END AS DutyName,
							   TD.dDateTime AS DutyDate,
							   @schedulingpersonid schedulingpersonid
						  FROM TimeDimension TD
						  LEFT JOIN @TempAllocationsWTD TW  ON TW.dutydate = TD.dDateTime
														 AND TW.schedulingpersonid = @schedulingpersonid
														 AND TW.DutyName NOT IN ('LEAVE','-')  
						WHERE TD.dDateTime > @WTD_DutyDate 
						  AND TD.dDateTime <= @LeaveChkEnddate
					) SP
			 WHERE schedulingpersonid = @schedulingpersonid
			   AND DutyDate > @WTD_DutyDate
			   AND DutyDate <= @LeaveChkEnddate
			   AND UPPER(DutyName) NOT IN ('LEAVE','-')--,'OFF LEAVE') 



		    SELECT @LeaveDutyStartDate = MAX(DutyDate) 
			  FROM (
						SELECT CASE WHEN TW.DutyName IS NULL THEN 'U' ELSE TW.DutyName END AS DutyName,
							   TD.dDateTime AS DutyDate,
							   @schedulingpersonid schedulingpersonid
						  FROM TimeDimension TD
						  LEFT JOIN @TempAllocationsWTD TW  ON TW.dutydate = TD.dDateTime
														 AND TW.schedulingpersonid = @schedulingpersonid
														 AND TW.DutyName NOT IN ('LEAVE','-')  
						WHERE TD.dDateTime < @WTD_DutyDate 
						  AND TD.dDateTime >= @LeaveChkStartdate
					) SP			  
			 WHERE schedulingpersonid = @schedulingpersonid
			   AND DutyDate <  @WTD_DutyDate
			   AND DutyDate >=  @LeaveChkStartdate
			   AND UPPER(DutyName) NOT IN ('LEAVE','-')--,'OFF LEAVE') 

            IF ( @LeaveDutyEndDate IS NOT NULL AND @LeaveDutyStartDate IS NOT NULL)
			 BEGIN

				 INSERT INTO @AllocationBreachDataTP  ( SchedulingTeamId,
													  SchedulingPersonID, 
													  breachtype,
													  startdate,
													  enddate, 
													  BreachedBy,
													  BreachedDate,
													  IsApproved,
													  History)
				 SELECT @pteamId as SchedulingTeamId,
					   fd.SchedulingPersonID as SchedulingPersonID, 
					   1 as breachtype,
					   cast(fd.startdate as date) as startdate,
					   cast(fd.enddate as date) as enddate, 
					   @vname as BreachedBy,
					   getdate() as BreachedDate,
					   Case when @workTimeDirectiveOptOut =1 then 1 else 0 END as IsApproved,
					   'WTD Type 1 Breached By '+@vname+' at ' + @HDate as History
				FROM (
				SELECT TD.SchedulingPersonID,
					   TD.DutyDate         as StartDate,
					   @LeaveDutyEndDate   as EndDate, 			
					   ( SELECT ( CASE WHEN TD.EndTime <= ET.StartTime 
										AND TD.EndTime < TD.StartTime THEN ET.StartTime - TD.EndTime
									   WHEN TD.EndTime >= ET.StartTime 
										AND TD.EndTime < TD.StartTime 
										AND ET.StartTime < ET.endtime THEN TD.EndTime - ET.StartTime
									   WHEN TD.EndTime > ET.StartTime then (86400-TD.EndTime ) + ET.StartTime end ) as TurnaroundTime
						   FROM @TempAllocationsWTD ET
						  WHERE ET.SchedulingPersonID = TD.SchedulingPersonID
							AND et.DutyDate = @LeaveDutyEndDate
							AND NOT (ET.starttime=0 and ET.endtime=0)
							AND upper(ET.Dutyname) not in ('U-SICK','SICK','-SICK','LEAVE','ABSENT','U','OFF LEAVE')
					   ) AS TurnaroundTime,
					   0 AS AllocationID	   
				  from @TempAllocationsWTD  TD
				  WHERE schedulingpersonid = @schedulingpersonid
				    AND TD.DutyDate = @LeaveDutyStartDate 
					AND NOT (TD.starttime=0 and TD.endtime=0)
					AND upper(TD.Dutyname) not in ('U-SICK','SICK','-SICK','LEAVE','ABSENT','U','OFF LEAVE')
						) FD where CAST(isnull(TurnaroundTime,54000) AS FLOAT)/CAST(3600 AS FLOAT) < 11	
						
			 END
		 END

			SET @LeaveDutyStartDate = NULL
		    SET @LeaveDutyEndDate = NULL
			SET @WTD_DutyName = NULL
			SET @WTD_DutyDate = NULL
			SET @schedulingpersonid = NULL

		 	FETCH NEXT FROM CUR_WTD INTO @WTD_DutyName, @WTD_DutyDate, @schedulingpersonid
	  END
	    CLOSE CUR_WTD;

        DEALLOCATE CUR_WTD;  
	 END 

	INSERT INTO @AllocationBreachData  ( SchedulingTeamId,
										 SchedulingPersonID, 
										 breachtype,
										 startdate,
										 enddate, 
										 BreachedBy,
										 BreachedDate,
										 IsApproved,
										 History)
	SELECT DISTINCT SchedulingTeamId,
					SchedulingPersonID, 
					breachtype,
					startdate,
					enddate, 
					BreachedBy,
					BreachedDate,
					IsApproved,
					History
			   FROM @AllocationBreachDataTP
											  
											  
		    INSERT INTO @AllocationBreachDataTemp
			            ( SchedulingPersonID,
                          WeekNumber,
						  StartDate,
						  EndDate,
						  WeekTotal )			  
			SELECT AP.ASP_SchedulingPersonID,
				   AL.AL_WeekNumber,
				   min(AD.AD_DutyDate) AS StartDate,
				   max(AD.AD_DutyDate) AS EndDate,
				   CAST(Sum(Isnull(AD_Duration, 0)) AS FLOAT)/ ( CAST ( 3600 AS FLOAT) )  weektotal
			FROM  Allocations AL
			INNER JOIN AllocationsScheduledPersons AP on AL.AL_AllocationsID = AP.ASP_AllocationsID
			INNER JOIN AllocationsDuties AD on AD.AD_AllocationsDutyID = AP.ASP_AllocationsDutyID 
			INNER JOIN (SELECT Min (startweek) AS startweek,
								Max(endweek)    endweek
						FROM   (SELECT DISTINCT td.ixyearweek,
								(SELECT TOP 1 * FROM
								(SELECT DISTINCT TOP 16 td1.ixyearweek
								FROM   timedimension TD1
								WHERE  td1.ixyearweek < ( td.ixyearweek )
								ORDER  BY TD1.ixyearweek DESC) td2 order by 1) AS
												startweek,
								(SELECT TOP 1 * FROM
								(SELECT DISTINCT TOP 17 TD3.ixyearweek
								FROM   timedimension TD3
								WHERE  td3.ixyearweek >= ( td.ixyearweek )
								ORDER  BY TD3.ixyearweek) td4
												ORDER  BY TD4.ixyearweek DESC)
												AS endweek
								FROM   timedimension TD
								WHERE  ddatetime BETWEEN @pStartDate AND @pEndDate ) td) td4
					ON al.AL_WeekNumber BETWEEN td4.startweek AND td4.endweek
			 WHERE AL.AL_SchedulingTeamID = @PTeamID
			   AND AP.ASP_SchedulingPersonID = ISNULL(@pSchedulingPersonID,AP.ASP_SchedulingPersonID)
			   AND AL.AL_Status <> 9
			GROUP  BY AP.ASP_SchedulingPersonID, AL.AL_WeekNumber	
		
				

			BEGIN

				DECLARE CUR_Breach CURSOR FOR 
				SELECT DISTINCT td.ixyearweek,
								(SELECT TOP 1 *
								 FROM   (SELECT DISTINCT TOP 17 td1.ixyearweek
										 FROM   timedimension TD1
										 WHERE  td1.ixyearweek <= ( td.ixyearweek )
										 ORDER  BY TD1.ixyearweek DESC) td2 order by 1) AS startweek,
								td.ixyearweek    AS endweek
				FROM   timedimension TD
				WHERE  ddatetime BETWEEN @pStartDate AND @pEndDate 

				OPEN CUR_Breach

				FETCH NEXT FROM CUR_Breach INTO
				 @vWeekNumber,
				 @vStartWeek,
				 @vEndWeek
				
				WHILE @@FETCH_STATUS = 0
				  BEGIN
					
                    INSERT INTO @AllocationBreachData( SchedulingTeamId,
											   SchedulingPersonID, 
											   breachtype,
											   startdate,
											   enddate, 
											   BreachedBy,
											   BreachedDate,
											   IsApproved,
											   History)
					SELECT @pteamId as SchedulingTeamId,
						   fd.schedulingpersonid as SchedulingPersonID, 
						   3 as breachtype,
						   fd.StartDate as StartDate ,
						   fd.EndDate as EndDate, 
						   @vname as BreachedBy,
						   getdate() as BreachedDate,
						   CASE WHEN @workTimeDirectiveOptOut =1 THEN 1 ELSE 0 END AS  IsApproved,
						   'WTD Type 3 Breached By '+@vname+' '
				                   + CONVERT(VARCHAR, Getdate(), 108)
								   + ' On ' + CONVERT(VARCHAR, Getdate(), 103) as History	
					 FROM (
					SELECT schedulingpersonid,
						   min(StartDate) as StartDate,
						   max(EndDate) as EndDate,
						   Round(Sum(weektotal) / ( CAST( 17 AS FLOAT)), 0) AS weekavg
					  FROM @AllocationBreachDataTemp
					 WHERE weeknumber between @vStartWeek and @vEndWeek	
					 GROUP BY schedulingpersonid
					 ) FD where fd.weekavg > 48
						   
					FETCH NEXT FROM CUR_Breach INTO 
					 @vWeekNumber,
					 @vStartWeek,
					 @vEndWeek
				  
				  END
				  
				CLOSE CUR_Breach;

				DEALLOCATE CUR_Breach;  
				
			END	
			
			BEGIN

				DECLARE CUR_Breach CURSOR FOR 
				SELECT DISTINCT td.ixyearweek, td.ixyearweek    AS StartWeek,
								(SELECT TOP 1 *
								 FROM   (SELECT DISTINCT TOP 17 td1.ixyearweek
										 FROM   timedimension TD1
										 WHERE  td1.ixyearweek >= ( td.ixyearweek )
										 ORDER  BY TD1.ixyearweek ) td2 order by 1 desc) AS EndWeek								
				FROM   timedimension TD
				WHERE  ddatetime BETWEEN @pStartDate AND @pEndDate 

				OPEN CUR_Breach

				FETCH NEXT FROM CUR_Breach INTO
				 @vWeekNumber,
				 @vStartWeek,
				 @vEndWeek
				
				WHILE @@FETCH_STATUS = 0
				  BEGIN
					
                    INSERT INTO @AllocationBreachData( SchedulingTeamId,
											   SchedulingPersonID, 
											   breachtype,
											   startdate,
											   enddate, 
											   BreachedBy,
											   BreachedDate,
											   IsApproved,
											   History)
					SELECT @pteamId as SchedulingTeamId,
						   fd.schedulingpersonid as SchedulingPersonID, 
						   3 as breachtype,
						   fd.StartDate as StartDate ,
						   fd.EndDate as EndDate, 
						   @vname as BreachedBy,
						   getdate() as BreachedDate,
						   CASE WHEN @workTimeDirectiveOptOut =1 THEN 1 ELSE 0 END AS IsApproved,
						   'WTD Type 3 Breached By '+@vname+' '
				                   + CONVERT(VARCHAR, Getdate(), 108)
								   + ' On ' + CONVERT(VARCHAR, Getdate(), 103) as History	
					 FROM (
					SELECT schedulingpersonid,
						   min(StartDate) as StartDate,
						   max(EndDate) as EndDate,
						   Round(Sum(weektotal) / ( CAST( 17 AS FLOAT) ), 0) AS weekavg
					  FROM @AllocationBreachDataTemp
					 WHERE weeknumber between @vStartWeek and @vEndWeek	
					 GROUP BY schedulingpersonid
					 ) FD where fd.weekavg > 48
						   
					FETCH NEXT FROM CUR_Breach INTO 
					 @vWeekNumber,
					 @vStartWeek,
					 @vEndWeek
				  
				  END
				  
				CLOSE CUR_Breach;

				DEALLOCATE CUR_Breach;  
				
			END				
			
				INSERT INTO working_time_directive( 
					   SchedulingPersonID, BreachType, StartDate,EndDate,
					   BreachedBy,BreachedDate,IsApproved,Comments,History )
				SELECT SchedulingPersonID,breachtype,StartDate,
					   EndDate,BreachedBy,BreachedDate,IsApproved,
					   CASE WHEN IsApproved =1 THEN  'Automatically approved due to team settings'
			          ELSE NULL END ,			   
			          CASE WHEN IsApproved =1 THEN  history +' <br> Comment added due to auto approval at '+ FORMAT(Getdate(),'HH:mm')
						        + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy' )
			          ELSE History END
				 FROM @AllocationBreachData AD
				 WHERE NOT EXISTS (
								   SELECT 1 
									 FROM working_time_directive WD
									WHERE WD.SchedulingPersonID = AD.SchedulingPersonID
									  AND WD.breachtype = AD.breachtype
									  AND WD.Startdate = AD.Startdate
									  AND WD.enddate = AD.enddate
								   );	

				WITH DBD (ID)
				AS
				(SELECT ID
				   FROM working_time_directive WD
				  WHERE WD.startdate >= DATEADD(DAY,-1, @pStartDate )
				    AND WD.EndDate <= DATEADD(DAY,1, @pEndDate ) 
				    AND DATEDIFF(DAY,WD.StartDate, WD.EndDate) = 1
				    AND WD.breachtype = 1
					AND EXISTS ( SELECT 1 FROM @TempAllocationsWTD TD WHERE WD.SchedulingPersonID = TD.schedulingpersonid  )
				    AND NOT EXISTS ( SELECT 1 
									   FROM @AllocationBreachData AD
									  WHERE WD.SchedulingPersonID = AD.SchedulingPersonID
									    AND WD.breachtype = AD.breachtype
									    AND WD.Startdate = AD.Startdate
									    AND WD.enddate = AD.enddate
										AND DATEDIFF(DAY, AD.startdate ,AD.enddate) = 1)
					)
					DELETE from DBD	;	

				WITH DBD (ID)
				AS
				(SELECT ID
				   FROM working_time_directive WD
				  WHERE WD.startdate >= DATEADD(DAY,-21,@pStartDate)
		            AND WD.EndDate <= DATEADD(DAY,21,@pEndDate)
				    AND DATEDIFF(DAY,WD.StartDate, WD.EndDate) > 1
				    AND WD.breachtype = 1
					AND EXISTS ( SELECT 1 FROM @TempAllocationsWTD TD WHERE WD.SchedulingPersonID = TD.schedulingpersonid  )
				    AND NOT EXISTS ( SELECT 1 
									   FROM @AllocationBreachData AD
									  WHERE WD.SchedulingPersonID = AD.SchedulingPersonID
									    AND WD.breachtype = AD.breachtype
									    AND WD.Startdate = AD.Startdate
									    AND WD.enddate = AD.enddate
										AND DATEDIFF(DAY, AD.startdate ,AD.enddate) > 1)
					)
					DELETE from DBD	;	

				WITH WBD (ID)
				AS
				(SELECT ID
				FROM working_time_directive WD
				where WD.startdate >= DATEADD(DAY, -6 , @pStartDate )
				  AND WD.EndDate <= DATEADD(DAY, 6 , @pEndDate )
				  AND WD.breachtype = 4		
				  AND EXISTS ( SELECT 1 FROM @TempAllocationsWTD TD WHERE WD.SchedulingPersonID = TD.schedulingpersonid  )
				  AND NOT EXISTS ( SELECT 1 
									 FROM @AllocationBreachData AD
									WHERE WD.SchedulingPersonID = AD.SchedulingPersonID
									  AND WD.breachtype = AD.breachtype
									  AND WD.Startdate = AD.Startdate
									  AND WD.enddate = AD.enddate)
					)
					DELETE from WBD;
					
					select @vminwtdweek = min(ddatetime), 
						   @vmaxwtdweek = max(ddatetime)
						from TimeDimension TD,
						(		SELECT DISTINCT td.ixyearweek,
											(SELECT TOP 1 *
											 FROM   (SELECT DISTINCT TOP 16 td1.ixyearweek
													 FROM   timedimension TD1
													 WHERE  td1.ixyearweek < ( td.ixyearweek )
													 ORDER  BY TD1.ixyearweek DESC) td2 order by 1) AS startweek,
											(SELECT TOP 1 *
											 FROM   (SELECT DISTINCT TOP 17 TD3.ixyearweek
													 FROM   timedimension TD3
													 WHERE  td3.ixyearweek >= ( td.ixyearweek )
													 ORDER  BY TD3.ixyearweek) td4
											 ORDER  BY TD4.ixyearweek DESC)              AS endweek
							FROM   timedimension TD
							WHERE  ddatetime BETWEEN @pStartDate AND @pEndDate
						) TD1 where TD.ixYearWeek between TD1.startweek and TD1.endweek	;	

				WITH SWBD (ID)
				AS
				(SELECT ID
				FROM working_time_directive WD
				where WD.startdate >= @vminwtdweek
				  AND WD.EndDate <= @vmaxwtdweek
				  AND WD.breachtype = 3					  
				  AND EXISTS ( SELECT 1 FROM @TempAllocationsWTD TD WHERE WD.SchedulingPersonID = TD.schedulingpersonid  )
				  and not exists ( SELECT 1 
									 FROM @AllocationBreachData AD
									WHERE WD.SchedulingPersonID = AD.SchedulingPersonID
									  AND WD.breachtype = AD.breachtype
									  AND WD.Startdate = AD.Startdate
									  AND WD.enddate = AD.enddate)
					)
					DELETE from SWBD;
					
		  --SELECT 0 as SPExecStatus,
			     --'Success' as SPMessage 
		RETURN 0
					
    END TRY
    BEGIN CATCH

		 INSERT INTO ErrorLog
		        (ErrorNumber,
				 ErrorState,
				 ErrorSeverity,
				 ErrorProcedure,
				 ErrorLine,
				 ErrorMessage,
				 ErrorDateTime,
				 UserName
				)
         SELECT ERROR_NUMBER() AS ErrorNumber,
                ERROR_STATE() AS ErrorState,
				ERROR_SEVERITY() AS ErrorSeverity,
				ERROR_PROCEDURE() AS ErrorProcedure,
				ERROR_LINE() AS ErrorLine,
				ERROR_MESSAGE() AS ErrorMessage,
				getutcdate(),
				@vuserID	
				
		RETURN 1

		 --SELECT @@IDENTITY as SPExecStatus,
			    --ERROR_MESSAGE() as SPMessage  
	
    END CATCH
		
END