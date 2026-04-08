USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_ReadAllocationsEditWeekly_FilterCount]    Script Date: 08/05/2024 22:45:11 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE OR ALTER            PROCEDURE [dbo].[usp_get_ReadAllocationsEditWeekly_FilterCount]
@startDate VARCHAR(50),
@endDate VARCHAR(50),
@schedulingTeamId VARCHAR(10),
@filterCond VARCHAR(MAX)

AS 
BEGIN

    SET NOCOUNT ON
    SET DATEFORMAT YMD

    DECLARE @FilterSQL          NVARCHAR(MAX)
    DECLARE @SelectList         NVARCHAR(MAX)
	DECLARE @VFilter            NVARCHAR(MAX)
	DECLARE @VLJFilter          NVARCHAR(MAX)	
    DECLARE @pWeekNumber        INT
	DECLARE @FilterSetFlag      INT = 0
	DECLARE @vleftjoinflag      INT = 0	
	DECLARE @ColumnFlag         INT = 0
	DECLARE @vCountColumn       VARCHAR(50)
	
	SET @EndDate = CONVERT(DATETIME,@EndDate,101)  - 1	


	 
		   SELECT AL.ID AS ID,
		          al.dutyname                                    AS DutyName,
		          ISNULL(AL.SortCode,ISNULL(spl.sortcode,''))    AS SortCode,
				  ISNULL(scp.CostCode,0)                         AS CostCode,
				  sd.staffid                                     AS StaffID,
				  ISNULL(AL.dutyProgramId,0)                     AS dutyProgramId,
				   ( CASE
					   WHEN ( sp.displayname IS NULL ) THEN
						 CASE
						   WHEN ( sd.preferredforename IS NULL
								   OR sd.preferredforename = '''' ) THEN (
						   sd.forename + '''' + sd.surname )
						   ELSE ( sd.preferredforename + '''' + sd.surname )
						 END
					   ELSE sp.displayname
					 END )                                     AS DisplayName	
                  INTO #TempGetAllocations				  
			  FROM Allocations AS AL
			 INNER join Timedimension TD on AL.WeekNumber=TD.ixYearWeek and AL.iDay = TD.ixDayInWeek
			 INNER JOIN ScheduledPeople AS sp (nolock) ON sp.scheduledpersonid = AL.schedulingpersonid
			 INNER JOIN ScheduledPersonTeam_LINK (nolock) AS spl ON sp.scheduledpersonid = spl.scheduledpersonid
							  AND spl.teamid=AL.SchedulingTeamId
			  LEFT JOIN StaffDetails sd (nolock) ON sd.staffid = sp.staffdetailsid
			  LEFT JOIN Staffconfig_Processed scp (nolock) ON sd.staffid = scp.staffid
							 AND AL.dutydate BETWEEN ISNULL(scp.startdate,AL.dutydate)
							                AND ISNULL( scp.enddate,AL.dutydate)											
		     WHERE spl.scheduledType = 1
			   AND CONVERT(DATETIME,@startDate,101) <=  isnull(spl.enddate,CONVERT(DATETIME,@startDate,101) )
			   AND CONVERT(DATETIME,@EndDate,101) >= isnull(spl.startdate,CONVERT(DATETIME,@EndDate,101) ) 
			   AND TD.dDateTime between CONVERT(DATETIME,@startDate,101) and CONVERT(DATETIME,@EndDate,101) 
	           AND AL.SchedulingTeamId = @schedulingTeamId

		 BEGIN
		   		  
			  SET @VFilter = replace( @filterCond, 'sp.DisplayName', 'DisplayName')
			  SET @VFilter = replace( @VFilter, 'spl.SortCode', 'sortcode')
			  SET @VFilter = replace( @VFilter, 'a.DutyName', 'DutyName')
			  SET @VFilter = replace( @VFilter, 'sct.CostCode','CostCode') 
			  SET @VFilter = replace( @VFilter, 'a.dutyProgramId','dutyProgramId')	

			  IF ( CHARINDEX ('DisplayName',@VFilter) > 0  )
			   BEGIN
			     SET @SelectList = 'DisplayName '
				 SET @ColumnFlag = 1
			   END

			  IF ( CHARINDEX ('sortcode',@VFilter) > 0  )
			   BEGIN
			     
				 IF ( @ColumnFlag = 1 )
				  BEGIN
				    SET @SelectList = @SelectList + ', sortcode '
				  END
				 ELSE
				  BEGIN
				    SET @ColumnFlag = 1
					SET @SelectList = ' sortcode '
				  END

			   END

			  IF ( CHARINDEX ('dutyProgramId',@VFilter) > 0  )
			   BEGIN
			     
				 IF ( @ColumnFlag = 1 )
				  BEGIN
				    SET @SelectList = @SelectList + ', dutyProgramId '
				  END
				 ELSE
				  BEGIN
				    SET @ColumnFlag = 1
					SET @SelectList = ' dutyProgramId '
				  END

			   END

			  IF ( CHARINDEX ('DutyName',@VFilter) > 0  )
			   BEGIN

				 IF ( @ColumnFlag = 1 )
				  BEGIN
				    SET @SelectList = @SelectList + ', DutyName '
				  END
				 ELSE
				  BEGIN
				    SET @ColumnFlag = 1
					SET @SelectList = ' DutyName '
				  END

			   END

			  IF ( CHARINDEX ('CostCode',@VFilter) > 0  )
			   BEGIN

				 IF ( @ColumnFlag = 1 )
				  BEGIN
				    SET @SelectList = @SelectList + ', CostCode '
				  END
				 ELSE
				  BEGIN
				    SET @ColumnFlag = 1
					SET @SelectList = ' CostCode '
				  END

			   END
			  
			  IF ( CHARINDEX ('aj.JobName',@VFilter) > 0 OR CHARINDEX ('aj.ProgrammeId',@VFilter) > 0 )
			   BEGIN
			     
				 SET @vleftjoinflag = 1
				 
                  select AJ.AllocationID, AJ.JobName, AJ.ProgrammeID
				         INTO #TempJobs
				  from Allocations_Jobs AJ
				  INNER JOIN #TempGetAllocations AL ON AL.ID= AJ.AllocationID
				  
                  SET @VLJFilter = ' INNER JOIN #TempJobs ON #TempGetAllocations.ID=#TempJobs.AllocationID '
                                     									 
			   END	
			   
			  SET @VFilter = replace( @VFilter, 'aj.JobName', 'JobName')
			  SET @VFilter = replace( @VFilter, 'aj.ProgrammeId','ProgrammeId') 
			  
			  IF ( CHARINDEX ('JobName',@VFilter) > 0  )
			   BEGIN

				 IF ( @ColumnFlag = 1 )
				  BEGIN
				    SET @SelectList = @SelectList + ', JobName '
				  END
				 ELSE
				  BEGIN
				    SET @ColumnFlag = 1
					SET @SelectList = ' JobName '
				  END

			   END		

			  IF ( CHARINDEX ('ProgrammeId',@VFilter) > 0  )
			   BEGIN

				 IF ( @ColumnFlag = 1 )
				  BEGIN
				    SET @SelectList = @SelectList + ', ProgrammeId '
				  END
				 ELSE
				  BEGIN
				    SET @ColumnFlag = 1
					SET @SelectList = ' ProgrammeId '
				  END

			   END				   	  	

			  IF ( CHARINDEX ('spsl.programmes_id',@VFilter) > 0 )
			   BEGIN
			  
			    SET @vleftjoinflag = 1
			  
				  SELECT AL.ID, programmes_id
				         INTO #TempStaffSkills
				    FROM skills_programmes_staff_link spsl 
					INNER JOIN #TempGetAllocations AL ON spsl.staff_id = AL.StaffID 
					
                  SET @VLJFilter = ISNULL(@VLJFilter,'') + ' INNER JOIN #TempStaffSkills  ON #TempGetAllocations.ID=#TempStaffSkills.ID '
									 
			   END				  
		  
			  SET @VFilter = replace( @VFilter, 'spsl.programmes_id','programmes_id')	 
			  
			  IF ( CHARINDEX ('programmes_id',@VFilter) > 0  )
			   BEGIN

				 IF ( @ColumnFlag = 1 )
				  BEGIN
				    SET @SelectList = @SelectList + ', programmes_id '
				  END
				 ELSE
				  BEGIN
				    SET @ColumnFlag = 1
					SET @SelectList = ' programmes_id '
				  END

			   END				    
			  
			  IF (@vleftjoinflag = 1)
			    BEGIN
				
				  SET @FilterSQL = ' SELECT count(1) as AllocationCount FROM (
				                     SELECT DISTINCT '+@SelectList+
								 ' FROM #TempGetAllocations '
								 +@VLJFilter+' '+@VFilter+' ) FD '		 
				
				END
			  ELSE
			    BEGIN
				  
				  SET @FilterSQL = ' SELECT count(1) as AllocationCount FROM (
				                     SELECT DISTINCT '+@SelectList+
				                     ' FROM #TempGetAllocations WHERE 1=1 '+ @VFilter +' ) FD '
				
				END
			  
		  END	  
       
	   EXEC ( @FilterSQL )

END
