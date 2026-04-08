USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_UpdateOverTwelve]    Script Date: 3/24/2026 5:15:56 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER     PROCEDURE  [dbo].[usp_UpdateOverTwelve]
@AllocationsSPID           INT,
@pNetLogin              VARCHAR(30),
@pMarkOverTwelve        SMALLINT,
@pOverTwelveHrs         INT,
@pIsOverseasOverTwelve  BIT

AS
BEGIN

    SET NOCOUNT ON

    SET DATEFORMAT YMD
	
    DECLARE @vMarkOverTwelve        SMALLINT
    DECLARE @vOverTwelveHrs         INT
    DECLARE @vIsOverseasOverTwelve  BIT	
    DECLARE @vSQL                   VARCHAR(MAX)
	DECLARE @vHistory               NVARCHAR(MAX)	
	DECLARE @updateflag             INT = 0 
	DECLARE @vuserID                INT	
	DECLARE @vname					VARCHAR(100)
	DECLARE @ReturnValue			INT
	DECLARE @AllocationsID			INT
	DECLARE @AllocationsDutyID		INT

	
	BEGIN TRY
		
	  SELECT @vname =  UD_DisplayName,
			 @vuserID = UD_UserID
	    FROM UserDetails 
	   WHERE UD_NetLogin=@pNetLogin				
 
	  SELECT @vMarkOverTwelve       = CASE WHEN ASP_OverTwelveStatus = 1 THEN -1
									       WHEN ASP_OverTwelveStatus = 0 THEN 9
										   WHEN ASP_OverTwelveStatus = 9 THEN 0
										   WHEN ASP_OverTwelveStatus = 2 THEN 1
										   ELSE 9
									   END ,
	         @vOverTwelveHrs        = ASP_OverTwelveHrs,
			 @vIsOverseasOverTwelve = ASP_IsOverseasOverTwelve,
			 @AllocationsID = ASP_AllocationsID,
			 @AllocationsDutyID = ASP_AllocationsDutyID
	    FROM AllocationsScheduledPersons 
	   WHERE ASP_AllocationsSPID = @AllocationsSPID		  

	  SET @vSQL = 'UPDATE AllocationsScheduledPersons SET '
	  SET @vHistory = ''

	  IF ( ISNULL(@vMarkOverTwelve,0) <> ISNULL(@pMarkOverTwelve,0)  ) 
	   BEGIN 
		 SET @updateflag = 1	
		 SET @vSQL = @vSQL+' ASP_OverTwelveStatus = '+cast(CASE WHEN @pMarkOverTwelve = 1
																THEN 2
																WHEN @pMarkOverTwelve = 0
																THEN 9 END as varchar)+', '	
		 
		  IF ( ISNULL(@vMarkOverTwelve,0) IN (-1,0) AND  ISNULL(@pMarkOverTwelve,0) = 1  ) 
		   SET @vHistory = 'Marked For Over12 by '+@vname+' on ' 
		                   + FORMAT(Getdate(),'dd/MM/yyyy')+' at '
						   + FORMAT(Getdate(),'HH:mm')+'.'
		   
		  IF ( ISNULL(@vMarkOverTwelve,0) IN (-1,1) AND  ISNULL(@pMarkOverTwelve,0) = 0  ) 
		   SET @vHistory = 'UnMarked For Over12 by '+@vname+' on ' 
		                   + FORMAT(Getdate(),'dd/MM/yyyy')+' at '
						   + FORMAT(Getdate(),'HH:mm')+'.'	
		  
	   END	
	   
	  IF ( ISNULL(@vOverTwelveHrs,0) <> ISNULL(@pOverTwelveHrs,0)  ) 
	   BEGIN    
		 SET @vSQL = @vSQL+' ASP_OverTwelveHrs = '+cast(@pOverTwelveHrs as varchar)+', '	
		 SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
		 SET @vHistory = @vHistory+'Over12 Hours Amount changed from '
						 + right('0' + CAST( isnull(@vOverTwelveHrs,0) / 3600 AS varchar(2)),2) + ':'  
						 + right('0' + CAST( (isnull(@vOverTwelveHrs,0) % 3600)/60 AS varchar(2)),2)+' To '
						 + right('0' + CAST( isnull(@pOverTwelveHrs,0) / 3600 AS varchar(2)),2) + ':'  
						 + right('0' + CAST( (isnull(@pOverTwelveHrs,0) % 3600)/60 AS varchar(2)),2)+' by '		 
  		                 +@vname+' On ' + FORMAT(Getdate(),'dd/MM/yyyy')+' at '+FORMAT(Getdate(),'HH:mm')+'.'
		 SET @updateflag = 1			 
	   END	  		

	  IF ( ISNULL(@vIsOverseasOverTwelve,0) <> ISNULL(@pIsOverseasOverTwelve,0)  ) 
	   BEGIN 	   
		 SET @vSQL = @vSQL+' ASP_IsOverseasOverTwelve = '+cast(@pIsOverseasOverTwelve as varchar)+', '	
		 SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
		 SET @vHistory = @vHistory+'Overseas Deployment changed from '
		                 + case when @vIsOverseasOverTwelve = 0 THEN '[False]' ELSE '[True]' end + ' to '
		                 + case when @pIsOverseasOverTwelve = 0 THEN '[False]' ELSE '[True]' end + ' by '						 
		                 + @vname+' On ' + FORMAT(Getdate(),'dd/MM/yyyy')+' at '+FORMAT(Getdate(),'HH:mm')+'.'
		 SET @updateflag = 1			 
	   END	  
	   
      IF ( @updateflag = 1)
	   BEGIN
	   
		 SET @vSQL = @vSQL+' ASP_UpdatedBy = '+ CAST(@vuserID AS VARCHAR)
						                  +', ASP_UpdatedDate = getutcdate()
					          WHERE ASP_AllocationsSPID = '+cast(@AllocationsSPID as varchar)
		 EXEC (@vSQL)		 

			INSERT INTO history ( historytype,
								  attributeid,
								  HistorySubType,
								  datetime,
								  userid,
								  history )		
			SELECT ht.id AS historytype,
				   @AllocationsSPID AS attributeid,
				   'PH' AS HistorySubType,
				   getdate(),
				   @vuserID,
				   @vHistory
			  FROM HistoryTypes HT
			 WHERE historytype = 'AllocationScheduledPerson'	
			 
					EXEC @ReturnValue = usp_CreateAllocationsUpdate @AllocationsID,
													 @AllocationsDutyID,
													 @AllocationsSPID,
													 0,
													 @pNetLogin;		 


	   END	   
	   
		SELECT 0 AS RETURNVAL
			
	END TRY
				
	BEGIN CATCH
			
		SELECT 1 AS RETURNVAL
			
	END CATCH;			
END