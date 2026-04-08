USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_WTDSummary]    Script Date: 10/09/2025 14:14:45 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER    PROCEDURE  [dbo].[usp_get_WTDSummary]
	-- Add the parameters for the stored procedure here navi
	@Teams varchar(max),
	@group1 varchar(255)        = NULL,
	@group2 varchar(255)        = NULL,
	@wtdFromDate  varchar(50),
	@wtdToDate  varchar(50),
	@chargeCodes varchar(max)   = NULL,
	@staffNumbers varchar(max)  = NULL,
	@breechType int,
	@approvalStatus int,
	@Orderby varchar(100)       = NULL,
	@Orderbyvlue varchar(15)    = NULL
	
AS
BEGIN

	-- SET NOCOUNT ON added to prevent extra result sets from
    SET NOCOUNT ON;
	DECLARE @SQL1	   VARCHAR(MAX);
	DECLARE @SQL2	   VARCHAR(MAX);	
	DECLARE @WhereFlag BIT = 0;
	DECLARE @ColList   VARCHAR(100);


	SET @SQL1 =   '  SELECT sp.UD_DisplayName   AS DisplayName,
							st.schedulingTeamName,
							UD_StaffNumber as StaffNumber,
							UC_CostCode AS CostCode,
							wtd.SchedulingPersonID,
							wtd.ID,
							WTD_Types.[Rule] as BreachType,
							wtd.StartDate,
							wtd.EndDate,
							wtd.BreachedBy,
							wtd.BreachedDate,
							wtd.IsApproved,
							wtd.ApprovedBy,
							wtd.ApprovedDate,
							wtd.Comments
					FROM   working_time_directive as wtd ( nolock)
					INNER JOIN UserDetails as sp ( nolock) ON sp.UD_UserID = wtd.SchedulingPersonID
					INNER JOIN Scheduledpersonteam_link AS stl ( nolock) ON stl.ScheduledPersonID = sp.UD_UserID
					INNER JOIN Schedulingteams as ST (nolock) ON ST.schedulingTeamId = stl.TeamID
					INNER JOIN WTD_Types on WTD_Types.ID = wtd.BreachType
					LEFT JOIN UserConfigs as scp (nolock) on scp.UC_UserID = sp.UD_UserID
										AND scp.UC_StartDate <= wtd.EndDate
										AND scp.UC_EndDate >= wtd.StartDate
					WHERE  stl.TeamID IN ( '+@Teams+' )
					AND stl.IsHomeTeam = 1
					AND isnull(Cast(stl.EndDate AS DATE), wtd.StartDate) >= wtd.StartDate
					AND Cast (stl.StartDate AS DATE) <= wtd.EndDate
					AND wtd.StartDate <= Cast('''+@wtdToDate+''' AS DATE)
					AND wtd.EndDate >= Cast ('''+@wtdFromDate+''' AS DATE)  '
	IF(@breechType != -1)
		 BEGIN
			SET @SQL1 = @SQL1+' AND wtd.BreachType = '''+cast(@breechType AS varchar)+''''
		END

	IF(@approvalStatus != -1)
		 BEGIN
			SET @SQL1 = @SQL1+' AND wtd.IsApproved = '''+cast(@approvalStatus AS varchar)+''''
		END

	IF ( @group1 IS NULL AND @group2 IS NULL)
	 BEGIN	 
	    SET @SQL2 = ' SELECT * FROM ( '+@SQL1+') FD '	 
	 END
	
	 
	IF ( @group1 IS NOT NULL OR @group2 IS NOT NULL )
	 BEGIN	 
	    SET @SQL2 = ' SELECT '+ CASE WHEN @group1 IS NOT NULL AND @group2 IS NOT NULL THEN @group1+','+@group2
		                             WHEN @group1 IS NOT NULL AND @group2 IS NULL THEN @group1 		                             
				WHEN @group1 IS NULL AND @group2 IS NOT NULL THEN @group2 END
		
	  SET @SQL2 = @SQL2+' ,COUNT(ID) as Count'
		                    + ' FROM ( '+@SQL1+') FD '
			 
	 END	 
	
	IF ( @chargeCodes IS NOT NULL )
	 BEGIN
		SET @SQL2 = @SQL2+' WHERE CostCode IN ( '''+REPLACE(@chargeCodes,',',''',''') +''' ) ';
		SET @WhereFlag = 1
	END


	IF ( @staffNumbers IS NOT NULL AND @chargeCodes IS NOT NULL )
	  BEGIN
		SET @SQL2 = @SQL2+' AND StaffNumber IN ( '''+REPLACE(@staffNumbers,',',''',''')+''' ) ';
	  END
	IF ( @staffNumbers IS NOT NULL AND @chargeCodes IS NULL )
	  BEGIN
		SET @SQL2 = @SQL2+' WHERE StaffNumber IN ( '''+REPLACE(@staffNumbers,',',''',''')+''' ) ';
		SET @WhereFlag = 1
	  END	 
		  
	-- Group BY START--
	IF ( @group1 IS NOT NULL ) 
	  BEGIN 
		SET @SQL2 = @SQL2+' GROUP BY '+@group1
	  END	  
	IF ( @group2 IS NOT NULL AND @group1 IS NOT NULL ) 
	  BEGIN 
		SET @SQL2 = @SQL2+','+@group2
	  END	  
	IF ( @group2 IS NOT NULL AND @group1 IS NULL ) 
	  BEGIN 
		SET @SQL2 = @SQL2+' GROUP BY '+@group2
	  END	  
	  
	----GROUP BY END----
	IF(@Orderby IS NOT NULL AND @Orderbyvlue IS NOT NULL)
	 BEGIN  
		SET @SQL2 = @SQL2+' ORDER BY '+ @Orderby + ' ' + @Orderbyvlue + '' 
	 END
				  
	EXEC (@SQL2);
    
END